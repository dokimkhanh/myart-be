<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 *
 * @property int $id
 * @property int $user_id
 * @property int $art_id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Art $art
 * @property User $user
 *
 * @package App\Models
 */
class Comment extends Model
{
	protected $table = 'comments';

	protected $casts = [
		'user_id' => 'int',
		'art_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'art_id',
		'content'
	];

	public function art()
	{
		return $this->belongsTo(Art::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
