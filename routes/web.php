<?php

use Platform\Training\Livewire\Dashboard;
use Platform\Training\Livewire\Group;
use Platform\Training\Livewire\Training;
use Platform\Training\Livewire\Session;
use Platform\Training\Livewire\Instructor;
use Platform\Training\Livewire\Participant;
use Platform\Training\Livewire\Enrollment;

Route::get('/', Dashboard::class)->name('training.dashboard');
Route::get('/groups', Group\Index::class)->name('training.groups.index');
Route::get('/trainings', Training\Index::class)->name('training.trainings.index');
Route::get('/sessions', Session\Index::class)->name('training.sessions.index');
Route::get('/instructors', Instructor\Index::class)->name('training.instructors.index');
Route::get('/participants', Participant\Index::class)->name('training.participants.index');
Route::get('/enrollments', Enrollment\Index::class)->name('training.enrollments.index');
