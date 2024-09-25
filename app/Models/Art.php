<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Art
 *
 * @property int $id
 * @property string $content
 * @property string|null $image
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 * @property Collection|ArtLike[] $art_likes
 * @property Collection|Comment[] $comments
 *
 * @package App\Models
 */
class Art extends Model
{
	protected $table = 'art';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'content',
		'image',
		'user_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function likes()
	{
		return $this->hasMany(ArtLike::class);
	}

	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
}
