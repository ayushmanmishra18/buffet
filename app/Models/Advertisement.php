<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\BaseModel;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Advertisement extends BaseModel implements HasMedia
{
    use InteractsWithMedia;

    protected $table    = 'advertisements';
    protected $fillable = ['title', 'description', 'link', 'position', 'sort', 'status'];
    protected $casts    = ['status' => 'int', 'sort' => 'int'];

    // Disable the WatchableTrait audit columns — the advertisements table
    // does not have creator_id / editor_id columns.
    protected $auditColumn = false;

    // Position slot constants
    const POSITION_TOP        = 'top';
    const POSITION_AFTER_HERO = 'after_hero';
    const POSITION_MIDDLE     = 'middle';
    const POSITION_BOTTOM     = 'bottom';

    /**
     * Human-readable labels for each position slot.
     */
    public static function positions(): array
    {
        return [
            self::POSITION_TOP        => 'Top (Below Navbar)',
            self::POSITION_AFTER_HERO => 'After Hero Banner',
            self::POSITION_MIDDLE     => 'Middle (Between Sections)',
            self::POSITION_BOTTOM     => 'Bottom (Above Footer)',
        ];
    }

    /**
     * Get active advertisements for a given position, ordered by sort.
     */
    public static function activeForPosition(string $position)
    {
        return static::where('position', $position)
            ->where('status', Status::ACTIVE)
            ->orderBy('sort', 'asc')
            ->get();
    }

    /**
     * Image accessor — returns the Spatie media URL or a default placeholder.
     */
    public function getImageAttribute(): string
    {
        $url = $this->getFirstMediaUrl('advertisement');
        if (!empty($url)) {
            return asset($url);
        }
        // Fallback to a frontend-visible placeholder
        return asset('frontend/images/default/restaurant.png');
    }
}
