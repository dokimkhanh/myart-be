<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FollowerUser
 * 
 * @property int $id
 * @property int $user_id
 * @property int $follower_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class FollowerUser extends Model
{
	protected $table = 'follower_user';

	protected $casts = [
		'user_id' => 'int',
		'follower_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'follower_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
