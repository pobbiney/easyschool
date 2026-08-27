<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserCat
 * 
 * @property int $cat_id
 * @property string $cat_name
 * @property string $status
 *
 * @package App\Models
 */
class UserCat extends Model
{
    use BelongsToSchool;

	protected $table = 'user_cat';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;

	protected $fillable = [
        'school_id',
		'cat_name',
		'status'
	];
}
