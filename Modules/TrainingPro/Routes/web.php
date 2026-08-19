<?php

use Illuminate\Support\Facades\Route;
use Modules\TrainingPro\Http\Controllers\TrainingProController;
use Modules\TrainingPro\Http\Controllers\TrainingProSettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
	Route::resource('training', TrainingProController::class)->except(['show'])->names('trainingpro');
	Route::group(['prefix' => 'training'], function () {
		Route::get('/configuration', [TrainingProController::class, 'config'])->name('config.home');
		Route::get('/results', [TrainingProController::class, 'results'])->name('config.results');

		Route::post('/create-category', [TrainingProController::class, 'createCategory'])->name('config.createCategory');
		Route::get('/edit-category/{id}', [TrainingProController::class, 'editCategory'])->name('config.editCategory');
		Route::delete('/delete-category/{id}', [TrainingProController::class, 'destroyCategory'])->name('config.destroy');

		Route::post('/create-programme', [TrainingProController::class, 'createProgramme'])->name('config.createProgramme');
		Route::get('/edit-programme/{id}', [TrainingProController::class, 'editProgramme'])->name('config.editProgramme');
		Route::delete('/delete-programme/{id}', [TrainingProController::class, 'destroyProgramme'])->name('config.destroyProgramme');

		Route::post('/create-topic', [TrainingProController::class, 'createTopic'])->name('config.createTopic');
		Route::get('/edit-topic/{id}', [TrainingProController::class, 'editTopic'])->name('config.editTopic');
		Route::delete('/delete-topic/{id}', [TrainingProController::class, 'destroyTopic'])->name('config.destroyTopic');

		Route::post('/create-assessment', [TrainingProController::class, 'createAssessment'])->name('config.createAssessment');
		Route::get('/edit-assessment/{id}', [TrainingProController::class, 'editAssessment'])->name('config.editAssessment');
		Route::delete('/delete-assessment/{id}', [TrainingProController::class, 'destroyAssessment'])->name('config.destroyAssessment');

		Route::get('/show-qa/{id}', [TrainingProController::class, 'showQa'])->name('config.showQa');
		Route::get('/create-qa/{id}', [TrainingProController::class, 'createQa'])->name('config.createQa');
		Route::post('/store-qa', [TrainingProController::class, 'storeQa'])->name('config.storeQa');
		Route::get('/edit-qa/{aid}/{qid}', [TrainingProController::class, 'editQa'])->name('config.editQa');
		Route::delete('/destroy-qa/{id}', [TrainingProController::class, 'destroyQa'])->name('config.destroyQa');

		Route::get('/show-assignee/{id}', [TrainingProController::class, 'showAssignee'])->name('config.showAssignee');
		Route::get('/create-assignee', [TrainingProController::class, 'createAssignee'])->name('config.createAssignee');
		Route::post('/store-assignee', [TrainingProController::class, 'storeAssignee'])->name('config.storeAssignee');
		Route::get('/edit-assignee/{id}', [TrainingProController::class, 'editAssignee'])->name('config.editAssignee');
		Route::delete('/destroy-assignee/{id}', [TrainingProController::class, 'destroyAssignee'])->name('config.destroyAssignee');


		Route::get('/programmes/{id}', [TrainingProController::class, 'getProgrammes'])->name('config.getProgrammes');
		Route::get('/topics/{id}', [TrainingProController::class, 'getTopics'])->name('config.getTopics');
		Route::get('/by_category/{id}', [TrainingProController::class, 'byCategory'])->name('config.by_category');
		Route::get('/by_department/{id}/{desigId}', [TrainingProController::class, 'byDepartment'])->name('config.by_department');
		Route::get('/by_designation/{id}/{deptId}', [TrainingProController::class, 'byDesignation'])->name('config.by_designation');

		Route::get('/trainings', [TrainingProController::class, 'trainings'])->name('config.trainings');
		Route::get('/launch-training/{id}', [TrainingProController::class, 'startTraining'])->name('config.startTraining');
		Route::get('/exit-training/{id}', [TrainingProController::class, 'exitTraining'])->name('config.exitTraining');

		Route::get('/assessments', [TrainingProController::class, 'assessments'])->name('config.assessments');
		Route::get('/start/{id}', [TrainingProController::class, 'startAssessment'])->name('config.start');
		Route::get('/get-qa/{id}', [TrainingProController::class, 'getQans'])->name('config.get-qa');
		Route::post('/assessment-stamp', [TrainingProController::class, 'updateAssessmentStamp'])->name('config.assessment-stamp');
		Route::get('/finish', [TrainingProController::class, 'finishAssessment'])->name('config.finish');

		Route::group(
			['prefix' => 'settings'],
			function () {
				Route::resource('training-settings', TrainingProSettingsController::class)->names('trainingpro-settings');//->except(['show']);
			}
		);
	});
});
