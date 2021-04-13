<?php
namespace Spatie\Activitylog;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Spatie\Activitylog\Exceptions\CouldNotLogActivity;

class CauserResolver
{
    protected AuthManager $authManager;

    protected string $authDriver;

    /**
     * User defined callback to override default reslover logic
     */
    protected Closure | null $resolverOverride = null;

    /**
     * User defined model to override default reslover logic
     */
    protected Model | null $causerOverride = null;

    public function __construct(Repository $config, AuthManager $authManager)
    {
        $this->authManager = $authManager;

        $this->authDriver = $config['activitylog']['default_auth_driver'] ?? $this->authManager->getDefaultDriver();
    }

    /**
     * Reslove causer based different arguments first we'll check for override closure
     * Then check for the result causer if it valid. In other case will return the
     * override causer defined by the user or delgate to the getCauser() method
     *
     * @param Model|int|null $subject
     * @return null|Model
     * @throws InvalidArgumentException
     * @throws CouldNotLogActivity
     */
    public function resolve(Model | int | null $subject = null) : ?Model
    {
        if ($this->resolverOverride !== null) {
            $resultCauser = call_user_func($this->resolverOverride);

            if (! $this->isResolveable($resultCauser)) {
                throw CouldNotLogActivity::couldNotDetermineUser($resultCauser);
            }

            return $resultCauser;
        }

        if ($this->causerOverride !== null) {
            return $this->causerOverride;
        }

        return $this->getCauser($subject);
    }


    /**
    * Resolve the user based on passed id
    *
    * @param int $subject
    * @return Model
    * @throws InvalidArgumentException
    * @throws CouldNotLogActivity
    */
    protected function resolveUsingId(int $subject) : Model
    {
        $guard = $this->authManager->guard($this->authDriver);

        $provider = method_exists($guard, 'getProvider') ? $guard->getProvider() : null;
        $model = method_exists($provider, 'retrieveById') ? $provider->retrieveById($subject) : null;


        throw_unless($model instanceof Model, CouldNotLogActivity::couldNotDetermineUser($subject));

        return $model;
    }

    /**
     * Return the subject if it was model. If the subject wasn't set will try to resolve
     * current authenticated user using the auth manager, else resolve the causer
     * from users table using id else throw couldNotDetermineUser exception
     *
     * @param Model|int|null $subject
     * @return null|Model
     * @throws InvalidArgumentException
     * @throws CouldNotLogActivity
     */
    protected function getCauser(Model | int | null $subject = null): ?Model
    {
        if ($subject instanceof Model) {
            return $subject;
        }

        if (is_null($subject)) {
            return $this->getDefaultCauser();
        }

        return $this->resolveUsingId($subject);
    }

    public function resolveUsing(Closure $callback): static
    {
        $this->resolverOverride = $callback;

        return $this;
    }

    public function setCauser(Model $causer): static
    {
        $this->causerOverride = $causer;

        return $this;
    }

    protected function isResolveable(mixed $model): bool
    {
        return ($model instanceof Model || is_null($model));
    }

    protected function getDefaultCauser(): ?Model
    {
        return $this->authManager->guard($this->authDriver)->user();
    }
}
