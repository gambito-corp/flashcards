<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;
    use softDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * Relación muchos a muchos con usuarios (vía team_user).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->using(TeamUser::class)
            ->withTimestamps();
    }

    public function editor()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->using(TeamUser::class)
            ->withTimestamps();
    }

    /**
     * Relación muchos a muchos con exams (vía exam_team).
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_team')
            ->using(ExamTeam::class)
            ->withTimestamps();
    }
    // Relación para las "carreras": un team tiene muchas áreas
    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
