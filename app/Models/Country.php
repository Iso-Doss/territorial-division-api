<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCountry
 */
class Country extends Model
{
    use HasFactory, softDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'description',
        'devise',
        'anthem_name', // Hymne national
        'national_holiday_name',
        'national_holiday_date',
        'republic_president_name',
        'republic_vice_president_name',
        'parliament_name',
        'official_language',
        'political_capital_name',
        'economic_capital_name',
        'largest_city_name',
        'total_area',
        'water_area',
        'time_zone',
        'nationality',
        'total_population',
        'density',
        'currency',
        'internet_domain',
        'activated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get regions.
     *
     * @return BelongsToMany That belongs to many.
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class)->withTimestamps();
    }

    /**
     * Get languages.
     *
     * @return BelongsToMany That belongs to many.
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class)->withTimestamps();
    }

    /**
     * Get international organisations.
     *
     * @return BelongsToMany That belongs to many.
     */
    public function internationalOrganisations(): BelongsToMany
    {
        return $this->belongsToMany(InternationalOrganisation::class)->withTimestamps();
    }
}
