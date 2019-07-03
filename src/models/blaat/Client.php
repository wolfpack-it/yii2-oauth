<?php

namespace WolfpackIT\oauth\models\activeRecord;

use JCIT\behaviors\BlameableBehavior;
use JCIT\behaviors\TimestampBehavior;
use WolfpackIT\oauth\components\AccessTokenService;
use WolfpackIT\oauth\components\ClientHttpBearerAuth;
use WolfpackIT\oauth\interfaces\ClientEntityInterface;
use WolfpackIT\oauth\models\ActiveRecord;
use WolfpackIT\oauth\queries\activeQuery\ClientQuery;
use WolfpackIT\oauth\traits\IdentifiableTrait;
use yii\helpers\ArrayHelper;
use yii\validators\RangeValidator;
use yii\validators\RegularExpressionValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\web\IdentityInterface;

/**
 * Class Client
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $id
 * @property string $identifier
 * @property string $secret
 * @property string $name
 * @property boolean $status
 * @property int $grant_type
 * @property-read ClientRedirect[] $clientRedirects
 * @property-read string $displayStatus
 * @property-read ClientGrantType[] $clientGrantTypes
 * @property-read ClientScope[] $clientScopes
 */
class Client
    extends ActiveRecord
    implements ClientEntityInterface, IdentityInterface
{
    use IdentifiableTrait;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_UPDATE = 'update';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @var string
     */
    protected $clientGrantGrantTypeClass = ClientGrantType::class;

    /**
     * @var string
     */
    protected $clientRedirectClass = ClientRedirect::class;

    /**
     * @var string
     */
    protected $clientScopeClass = ClientScope::class;

    /**
     * @var string
     */
    protected $scopeClass = Scope::class;

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => \Yii::t('app', 'Id'),
            'identifier' => \Yii::t('app', 'Identifier'),
            'secret' => \Yii::t('app', 'Secret'),
            'name' => \Yii::t('app', 'Name'),
            'status' => \Yii::t('app', 'Status'),
            'displayStatus' => \Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return array
     */
    public function attributeHints(): array
    {
        return [
            'name' => \Yii::t('app', 'The name users will see for this client.'),
            'identifier' => \Yii::t('app', 'A random string identifying the client, cannot be changed after creation.'),
            'secret' => \Yii::t('app', 'The secret of the client, like a password. Is automatically generated after creation and cannot be changed.')
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\base\Exception
     */
    public function beforeSave($insert): bool
    {
        $result = parent::beforeSave($insert);

        if ($this->isNewRecord && empty($this->secret)) {
            $this->secret = $this->secretHash(\Yii::$app->security->generateRandomString());
        }

        return $result;
    }

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                BlameableBehavior::class => [
                    'class' => BlameableBehavior::class,
                    'createdByAttribute' => 'created_by',
                    'updatedByAttribute' => 'updated_by',
                    'deletedByAttribute' => 'deleted_by'
                ],
                TimestampBehavior::class => [
                    'class' => TimestampBehavior::class,
                    'createdAtAttribute' => 'created_at',
                    'updatedAtAttribute' => 'updated_at',
                    'deletedAtAttribute' => 'deleted_at',
                ]
            ]
        );
    }

    /**
     * @return bool|false|int
     */
    protected function deleteInternal()
    {
        if (!$this->beforeDelete()) {
            return false;
        }
        $oldScenario = $this->scenario;
        $this->scenario = self::SCENARIO_DELETE;
        $result = $this->save();
        $this->scenario = $oldScenario;
        return (int) $result;
    }

    /**
     * @return ClientQuery
     */
    public static function find(): ClientQuery
    {
        return \Yii::createObject(ClientQuery::class, [get_called_class()]);
    }

    /**
     * @param int|string $id
     * @throws \Exception
     */
    public static function findIdentity($id)
    {
        throw new \Exception('Client is not a real identity');
    }

    /**
     * @param $accessToken
     * @param null $type
     * @return Client|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function findIdentityByAccessToken($accessToken, $type = null)
    {
        if ($type === ClientHttpBearerAuth::class) {
            $accessTokenService = \Yii::createObject(AccessTokenService::class);
            $token = $accessTokenService->getToken($accessToken);
            if (!is_null($token)) {
                $clientId = $token->getClaim('aud');
                return self::findOne(['identifier' => $clientId]);
            }
        }

        return null;
    }

    /**
     * @return string|void
     * @throws \Exception
     */
    public function getAuthKey()
    {
        throw new \Exception('Client is not a real identity');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientGrantTypes()
    {
        return $this->hasMany($this->clientGrantGrantTypeClass, ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientRedirects()
    {
        return $this->hasMany($this->clientRedirectClass, ['client_id' => 'id'])
            ->inverseOf('client')
            ;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientScopes()
    {
        return $this->hasMany($this->clientScopeClass, ['client_id' => 'id']);
    }

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getIsConfidential(): bool
    {
        return is_null($this->secret);
    }

    /**
     * @return string
     */
    public function getDisplayStatus(): string
    {
        return $this->statusOptions()[$this->status];
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getRedirectUri(): array
    {
        return ArrayHelper::getColumn($this->clientRedirects, 'redirect_uri');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScopes(callable $callable)
    {
        return $this->hasMany(Scope::class, ['id' => 'scope_id'])
            ->via('clientScopes', $callable)
        ;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name', 'identifier', 'status'], RequiredValidator::class],
            [['name'], StringValidator::class, 'min' => 5],
            [['identifier'], StringValidator::class, 'min' => 10],
            [['identifier'], RegularExpressionValidator::class, 'pattern' => '/^[a-zA-Z0-9\-_\.]{10,}$/', 'message' => \Yii::t('app', '{attribute} can only contain a-z A-Z 0-9 and - or _.')],
            [['status'], RangeValidator::class, 'range' => array_keys($this->statusOptions())]
        ];
    }

    /**
     * @return array
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['name', 'identifier', 'status'],
            self::SCENARIO_DELETE => [],
            self::SCENARIO_UPDATE => ['name', 'status'],
        ];
    }

    /**
     * @param $secret
     * @return bool|string
     */
    public function secretHash($secret): string
    {
        return $secret;
    }

    /**
     * @param $hash
     * @return bool
     */
    public function secretVerify($hash): bool
    {
        return $hash === $this->secret;
    }

    /**
     * @return array
     */
    public function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => \Yii::t('app', 'Active'),
            self::STATUS_INACTIVE => \Yii::t('app', 'Inactive')
        ];
    }

    /**
     * @param string $authKey
     * @return bool|void
     * @throws \Exception
     */
    public function validateAuthKey($authKey)
    {
        throw new \Exception('Client is not a real identity');
    }
}