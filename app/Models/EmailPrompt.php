<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A prompt a worker gave the email agent — saved so they can reuse it via the
 * "Use previous prompts" picker.
 *
 * @property int $id
 * @property int $user_id
 * @property string $prompt
 * @property string|null $template
 */
#[Fillable(['user_id', 'prompt', 'template'])]
class EmailPrompt extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
