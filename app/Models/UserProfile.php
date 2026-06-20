<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $avatar_path
 * @property string|null $job_title
 * @property string|null $company
 * @property string|null $location
 * @property string|null $website
 * @property string|null $bio
 */
#[Fillable(['user_id', 'avatar_path', 'job_title', 'company', 'location', 'website', 'bio'])]
class UserProfile extends Model
{
    /**
     * The profile fields a user is invited to fill in, used to compute how
     * complete their profile is.
     *
     * @var list<string>
     */
    public const COMPLETABLE_FIELDS = ['avatar_path', 'job_title', 'company', 'location', 'website', 'bio'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The public URL of the uploaded avatar, if any.
     *
     * @return Attribute<string|null, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->avatar_path !== null
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
        );
    }

    /**
     * The share of completable fields that have been filled in (0–100).
     */
    public function completionPercent(): int
    {
        $filled = collect(self::COMPLETABLE_FIELDS)
            ->filter(fn (string $field): bool => filled($this->{$field}))
            ->count();

        return (int) round($filled / count(self::COMPLETABLE_FIELDS) * 100);
    }
}
