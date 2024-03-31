<?php

namespace Administration;

use Administration\Casts\Users;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use function Laravel\Prompts\error;

class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', // The role name
        'permissions', // The permissions of the role as a enum
        'include', // The resources and actions that the role has access to
        'exclude', // The resources and actions that the role does not have access to
        'users', // The users that have this role
    ];

    /**
     * The include and exclude attributes are cast to JSON arrays.
     *
     * These attributes are used to specify which resources and actions a role
     * has access to or does not have access to.
     *
     * @var array
     */
    protected $casts = [
        'include' => 'json',
        'exclude' => 'json',

        /**
         * The users attribute is cast to a Users class.
         *
         * This class takes care of casting the users array to an array of User
         * objects, and ensures that the users array is sorted alphabetically
         * by their id.
         *
         * @var Users
         */
        'users' => Users::class,
    ];

    /**
     * The current user ID.
     *
     * This is used to identify which user is being edited when calling the methods
     * that interact with the user's role.
     *
     * @var string|null
     */
    public $currentUser = null;

    /**
     * Retrieves a Role object by its name.
     *
     * @param string|int $role The name of the role to retrieve.
     * @return self The Role object with the specified name.
     */
    public static function _($role): self
    {
        return Role::where(is_int($role) ? 'id' : 'name', 'like', $role)->first();
    }

    /**
     * Retrieves the role of a user based on their ID or user object.
     *
     * @param mixed $user The user ID or user object.
     * @return self The role of the user.
     */
    public static function user(mixed $user) : self
    {
        if(is_object($user)) $id = (int) $user->id;
        else $id = (int) $user;
        $role = self::query()->where('users', 'like', '%"'.$id.'"%')->first();
        if(!$role) $role = self::_('member');
        $role->currentUser = (string) $id;
        return $role;
    }

    /**
     * A method to remove the current user role.
     *
     * @throws error User not found
     * @return self
     */
    public function remove() : self
    {
        if(!$this->currentUser) throw error('User not found');
        $this->users = array_values(array_diff($this->users->map(fn($user) => (string) $user->id)->toArray(), [$this->currentUser]));
        $this->save();
        return $this;
    }

    /**
     * Adds a role to the current user.
     *
     * @param string|Role $role The role to be added. It can be either a string or an instance of the Role class.
     * @throws Exception If the current user is not found.
     * @return self The updated Role instance.
     */
    public function add($role) : self
    {
        if(!$this->currentUser) throw error('User not found');
        if(is_string($role) || is_int($role)) $role = self::_($role);
        $role->users[] = $this->currentUser;
        $role->save();
        return $role;
    }

    /**
     * Changes the current user's role to a new role.
     *
     * @param string|Role $to_role The role name or Role instance that the user should be changed to.
     * @return self The updated Role instance.
     */
    public function change($to_role) : self
    {
        return $this->remove()->add($to_role);
    }
}
