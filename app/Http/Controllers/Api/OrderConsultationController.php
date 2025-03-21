<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderConsultation;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\VendorAnswers;
use Auth;
use DB;
use Illuminate\Http\Request;

class OrderConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $request->validate([
            'date' => 'required|date_format:Y-m-d', // Validates YYYY-MM-DD format
            'time' => 'required|date_format:H:i:s', // Validates HH:MM:SS format
            'consultaion_type_id' => 'required|exists:consultaion_type,id',
            'consultaion_id' => 'required|exists:consultaion,id',
            'quiz_id' => [
                        'required',
                        'exists:quizzes,id',
                        function ($attribute, $value, $fail) use ($request) {
                            $exists = DB::table('quizzes')
                                ->where('id', $value)
                                ->where('consultaion_id', $request->consultaion_id)
                                ->exists();

                            if (!$exists) {
                                $fail("The selected quiz_id is not associated with the given consultaion_id.");
                            }
                        }
                    ],
                  'answers' => 'required|array',
            'answers.*.question_id' => [
                'required',
                'exists:quezies_questions,id',
                function ($attribute, $value, $fail) use ($request) {
                    $quizId = $request->quiz_id;
                    $questionExists = QuizQuestion::where('id', $value)
                        ->where('quiz_id', $quizId)
                        ->exists();

                    if (!$questionExists) {
                        $fail("The question_id {$value} does not belong to quiz_id {$quizId}.");
                    }
                }
            ],
        'answers.*.value' => [
            'nullable',
            function ($attribute, $value, $fail) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (!is_numeric($item)) {
                            $fail("Each item in {$attribute} must be a valid answer ID.");
                            return;
                        }

                        if (!\DB::table('quezies_answers')->where('id', $item)->exists()) {
                            $fail("The selected {$attribute} contains an invalid answer ID.");
                            return;
                        }
                    }
                } else {
                    if (!is_numeric($value) && !is_string($value)) {
                        $fail("The {$attribute} must be either a valid answer ID or a string.");
                    }

                    if (is_numeric($value) && !\DB::table('quezies_answers')->where('id', $value)->exists()) {
                        $fail("The selected {$attribute} is invalid.");
                    }
                }
            },
        ],

        ]);

         // **Get Consultation Schedule ID**
    $schedule = DB::table('consultaion_schedual')
    ->whereDate('date', $request->date)
    ->whereTime('time', $request->time)
    ->first();
         if (!$schedule) {
            return $this->failure("No consultation schedule found for the provided date and time.");
        }

        DB::beginTransaction();
        try {
            foreach ($request->answers as $index => $answerData) { // Track index
                $question = QuizQuestion::find($answerData['question_id']);

                if (!$question) {
                  return  $this->failure( 'Invalid question_id provided');
                 }

                // Fetch all valid answer IDs for the question
                $validAnswerIds = QuizAnswer::where('question_id', $question->id)->pluck('id')->toArray();

                if ($question->type == 'text') {
                    VendorAnswers::create([
                        'vendor_id' => Auth::user()->id,
                        'quiz_id' => $request->quiz_id,
                        'question_id' => $question->id,
                        'answer_id' => null,
                        'text_answer' => $answerData['value'] ?? null,
                    ]);
                } elseif ($question->type == 'single' || $question->type == 'true_false') {
                    if (!in_array($answerData['value'], $validAnswerIds)) {
                        return $this->failure("Invalid answer_id at index $index for the given question_id");
                    }
                    VendorAnswers::create([
                        'vendor_id' => Auth::user()->id,
                        'quiz_id' => $request->quiz_id,
                        'question_id' => $question->id,
                        'answer_id' => $answerData['value'],
                    ]);
                } elseif ($question->type == 'multiple') {
                    foreach ($answerData['value'] as $answerId) {
                         if (!in_array($answerId, $validAnswerIds)) {
                            return $this->failure("'One of the answer_id values is invalid for the given  $index for the given question_id");

                         }
                        VendorAnswers::create([
                            'vendor_id' => Auth::user()->id,
                            'quiz_id' => $request->quiz_id,
                            'question_id' => $question->id,
                            'answer_id' => $answerId,
                        ]);
                    }
                }
            }
            // **Create an Order After Answering Questions**
            $order = Order::create([
                'vendor_id' => Auth::user()->id,
                'consultaion_id' => $request->consultaion_id,
                'consultaion_type_id' => $request->consultaion_type_id,
                'consultaion_schedual_id' => $schedule->id, // Assign schedule ID
                'quiz_id' => $request->quiz_id,
                'is_paid' => false,
                'type' => $request->type,
                'total_price' => $request->total_price,
                'payment_method' => $request->payment_method,
                'created_at' => $request->date . ' ' . $request->time, // Combine date & time
            ]);

            DB::commit();
            return $this->success('Order submitted successfully');

         } catch (\Exception $e) {
            DB::rollBack();
            return $this->failure($e->getMessage());
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderConsultation  $orderConsultation
     * @return \Illuminate\Http\Response
     */
    public function show(OrderConsultation $orderConsultation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderConsultation  $orderConsultation
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderConsultation $orderConsultation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderConsultation  $orderConsultation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderConsultation $orderConsultation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderConsultation  $orderConsultation
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderConsultation $orderConsultation)
    {
        //
    }
}
