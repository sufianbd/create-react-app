<?php

namespace App\Http\Controllers\Api\V1;

use App\Modules\Survey\Models\Survey;
use App\Modules\Survey\Models\SurveyAnswer;
use App\Modules\Survey\Models\SurveyQuestion;
use App\Modules\Survey\Models\SurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyApiController extends ApiController
{
    /**
     * GET /api/v1/surveys
     */
    public function surveys(Request $request): JsonResponse
    {
        $tenantId = app()->has('tenant') ? app('tenant')->id : $request->user()->tenant_id;

        $query = Survey::where('tenant_id', $tenantId);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->latest()->paginate(20);

        return $this->paginated($paginator);
    }

    /**
     * GET /api/v1/surveys/{id}
     */
    public function showSurvey(int $id): JsonResponse
    {
        $survey = Survey::with(['questions' => function ($q) {
            $q->orderBy('sequence');
        }])->findOrFail($id);

        return $this->success($survey);
    }

    /**
     * POST /api/v1/surveys
     */
    public function storeSurvey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|string|in:draft,active,closed',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
            'questions'   => 'nullable|array',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.question_type' => 'required_with:questions|string|in:text,multiple_choice,rating,boolean',
            'questions.*.is_required'   => 'nullable|boolean',
            'questions.*.sequence'      => 'nullable|integer|min:1',
            'questions.*.options'       => 'nullable|array',
        ]);

        $tenantId = app()->has('tenant') ? app('tenant')->id : $request->user()->tenant_id;

        $questions = $validated['questions'] ?? [];
        unset($validated['questions']);

        $validated['tenant_id']  = $tenantId;
        $validated['created_by'] = $request->user()->id;
        $validated['status']     = $validated['status'] ?? 'draft';

        $survey = Survey::create($validated);

        foreach ($questions as $index => $q) {
            $survey->questions()->create([
                'tenant_id'     => $tenantId,
                'question_text' => $q['question_text'],
                'question_type' => $q['question_type'],
                'is_required'   => $q['is_required'] ?? false,
                'sequence'      => $q['sequence'] ?? ($index + 1),
                'options'       => $q['options'] ?? null,
            ]);
        }

        return $this->success($survey->load('questions'), 201);
    }

    /**
     * POST /api/v1/surveys/{id}/respond
     */
    public function submitResponse(Request $request, int $id): JsonResponse
    {
        $survey = Survey::findOrFail($id);

        $validated = $request->validate([
            'respondent_name'  => 'nullable|string|max:255',
            'respondent_email' => 'nullable|email|max:255',
            'answers'          => 'required|array',
            'answers.*.survey_question_id' => 'required|integer|exists:survey_questions,id',
            'answers.*.answer_text'        => 'nullable|string',
            'answers.*.answer_options'     => 'nullable|array',
        ]);

        $tenantId = app()->has('tenant') ? app('tenant')->id : $request->user()->tenant_id;

        $response = SurveyResponse::create([
            'tenant_id'        => $tenantId,
            'survey_id'        => $survey->id,
            'respondent_name'  => $validated['respondent_name'] ?? null,
            'respondent_email' => $validated['respondent_email'] ?? null,
            'submitted_at'     => now(),
        ]);

        foreach ($validated['answers'] as $answer) {
            SurveyAnswer::create([
                'tenant_id'          => $tenantId,
                'survey_response_id' => $response->id,
                'survey_question_id' => $answer['survey_question_id'],
                'answer_text'        => $answer['answer_text'] ?? null,
                'answer_options'     => $answer['answer_options'] ?? null,
            ]);
        }

        return $this->success($response->load('answers'), 201);
    }
}
