<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class Bag.
 * 
 * @author Yonathan <yonathan.4220230017@civitas.ukrida.ac.id>
 * 
 * @OA\Schema(
 *      description="Bag model",
 *      title="Bag model",
 *      required={"title", "author"},
 *      @OA\Xml(
 *          name="Bag"
 *      )
 * )
 */

class Bag extends Model
{
    // use HasFactory;
    use SoftDeletes;
    protected $table = 'bags';
    protected $fillable = [
        'name',
        'designer',
        'publisher',
        'publication_year',
        'cover',
        'description',
        'price',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public function data_adder(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}