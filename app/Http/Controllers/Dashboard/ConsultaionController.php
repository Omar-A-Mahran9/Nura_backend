<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreConsultationRequest;
use App\Models\Consultaion;
use App\Models\ConsultaionSchedual;
use App\Models\ConsultaionType;
use App\Models\Order;
use App\Models\VendorAnswers;
use Illuminate\Http\Request;
use MacsiDigital\Zoom\Facades\Zoom;

class ConsultaionController extends Controller
{
     public function index(Request $request)
    {
         $this->authorize('view_consultation_time');

        if ($request->ajax())
        {
            $data = getModelData(new Consultaion());

             return response()->json($data);
        }

        return view('dashboard.consultaiondata.index');
    }


    public function create()
    {
        $this->authorize('create_consultation_time');

        // Get types that are NOT used in any consultation
        $types = ConsultaionType::whereDoesntHave('consultaions')
            ->select('id', 'name_' . getLocale())
            ->get();

        return view('dashboard.consultaiondata.create', compact('types'));
    }



    public function store(StoreConsultationRequest $request)
    {
        // Get the validated data
        $data = $request->validated();

        // Handle the main image upload
        if ($request->file('main_image')) {
            $data['main_image'] = uploadImage($request->file('main_image'), "Consultation");
        }

        // Create consultation record
        $consultaion = Consultaion::create([
            'title_ar' => $data['title_ar'],
            'title_en' => $data['title_en'],
            'description_ar' => $data['description_ar'],
            'description_en' => $data['description_en'],
            'main_image' =>  $data['main_image'],
            'price' =>  $data['price'],
            'consultaion_type_id' => $data['consultaion_type_id'],
        ]);

        // Handle consultation schedule times
        $consultaiontimes = $request->time_list ?? [];
        $zoomMeetings = [];

        if (is_array($consultaiontimes) && count($consultaiontimes) > 0) {
            foreach ($consultaiontimes as $consultaiontime) {
                $available = isset($consultaiontime['available']) &&
                is_array($consultaiontime['available']) &&
                count($consultaiontime['available']) > 0 &&
                $consultaiontime['available'][0] === 'true' ? 1 : 0;

                // Create Zoom meeting for this schedule
                $meetingData = [
                    'topic'      => "Consultation: " . $consultaion->title_en,
                    'start_time' => $consultaiontime['date'] . 'T' . $consultaiontime['time'],
                    'duration'   => 60, // Default 60 minutes
                    'agenda'     => "Scheduled consultation session",
                ];

                $meeting = $this->createMeeting($meetingData);

                // Create consultation schedule with Zoom details
                $section = ConsultaionSchedual::create([
                    'consultaion_id' => $consultaion->id,
                    'date' => $consultaiontime['date'],
                    'time' => $consultaiontime['time'],
                    'available' => $available,
                    'zoom_meeting_id' => $meeting->id,
                    'zoom_join_url' => $meeting->join_url,
                    'zoom_start_url' => $meeting->start_url,
                ]);

                $zoomMeetings[] = [
                    'meeting_id' => $meeting->id,
                    'join_url' => $meeting->join_url,
                    'start_url' => $meeting->start_url,
                ];
            }
        }

    }


    public function update(Request $request,$id)
    {
        $consultaion = Consultaion::with('consultaionScheduals')->findOrFail($id);
        // Validate request data
        $data = $request->validate([
            'main_image'      => ['nullable', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],

            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric',
            'consultaion_type_id' => 'nullable|exists:consultaion_types,id',
            'time_list' => 'nullable|array',
            'time_list.*.date' => 'required_with:time_list|date|after_or_equal:today',
            'time_list.*.time' => 'required_with:time_list',
        ]);

                 // Handle the main image upload
                 if ($request->file('main_image')) {
                    $data['main_image'] = uploadImage($request->file('main_image'), "Consultation");
                }

        // Update consultation data
        $consultaion->update([
            'main_image'=>$data['main_image'],
            'title_ar' => $data['title_ar'],
            'title_en' => $data['title_en'],
            'description_ar' => $data['description_ar'],
            'description_en' => $data['description_en'],
            'price' => $data['price'],
            'consultaion_type_id' => $data['consultaion_type_id'] ?? $consultaion->consultaion_type_id, // ✅ Fix for missing key
        ]);

        // Remove old schedules before inserting new ones
        $consultaion->consultaionScheduals()->delete();
        if (!empty($request->time_list)) {
            foreach ($request->time_list as $time) {
                $available = isset($time['available']) &&
                is_array($time['available']) &&
                count($time['available']) > 0 &&
                $time['available'][0] === 'true' ? 1 : 0;

                $consultaion->consultaionScheduals()->create([
                    'date' => $time['date'],
                    'time' => $time['time'],
                    'available' =>    $available ,
                ]);
            }
        }

    }




    public function show($id)
    {
        $types = ConsultaionType::select('id', 'name_' . getLocale())->get();
        $consultaion = Consultaion::with('consultaionScheduals')->findOrFail($id);
        $this->authorize('update_consultation_time');

        return view('dashboard.consultaiondata.show', compact('consultaion', 'types'));
    }

    public function edit($id)
    {
        $types = ConsultaionType::select('id', 'name_' . getLocale())->get();
        $consultaion = Consultaion::with('consultaionScheduals')->findOrFail($id);
        $this->authorize('update_consultation_time');

        return view('dashboard.consultaiondata.edit', compact('consultaion', 'types'));
    }

    public function order(Request $request)
    {
           $this->authorize('view_orders');

        if ($request->ajax())
        {
                $data = getModelData(model: new Order(),relations: ['vendor' => ['id', 'name','phone']], andsFilters: [['type', '=', 'consultation']]);


            return response()->json($data);
        }

        return view('dashboard.orders.index');
    }

    public function order_show($id)
    {
        $this->authorize('view_orders'); // Authorization check

        // Fetch order with all related data
        $order = Order::with([
            'vendor:id,name,phone',
            'book:id,title_ar,title_en',
            'course:id,name_ar,name_en',
            'consultation:id,title_ar,title_en',
            'consultaionType:id,name_ar,name_en',
            'consultaionSchedual:id,time,date',
            'quiz:id,name_ar,name_en',
            'quiz.questions.answers', // Include questions and answers
        ])->findOrFail($id);

        // Fetch vendor answers related to this quiz and client
        $vendorAnswers = $order->quiz
        ? VendorAnswers::where('quiz_id', $order->quiz->id)
            ->where('vendor_id', $order->vendor_id)
            ->get()
        : collect(); // Return an empty collection if no quiz is found


        return view('dashboard.orders.show', compact('order', 'vendorAnswers'));
    }


    public function destroy(Request $request, $id)
    {
        $this->authorize('delete_articles');

        // Find the consultation with its schedules
        $consultaion = Consultaion::with('consultaionScheduals')->findOrFail($id);

        try {
            \DB::transaction(function () use ($consultaion) {
                // Delete related schedules first (if needed)
                $consultaion->consultaionScheduals()->delete();

                // Delete consultation
                $consultaion->delete();

                // Delete image
                deleteImage($consultaion->main_image, 'Consultation');
            });

            // Return JSON response if AJAX request
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Consultation deleted successfully']);
            }

         } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting consultation', 'error' => $e->getMessage()], 500);
            }

         }
    }


    public function createMeeting($data)
    {
        $user = Zoom::user()->first();

        $meeting = $user->meetings()->create([
            'topic'      => $data['topic'],
            'type'       => 2, // Scheduled Meeting
            'start_time' => $data['start_time'],
            'duration'   => $data['duration'], // in minutes
            'timezone'   => 'UTC',
            'agenda'     => $data['agenda'],
            'settings'   => [
                'host_video' => true,
                'participant_video' => true,
                'mute_upon_entry' => true,
                'waiting_room' => true,
            ]
        ]);

        return $meeting;
    }


}
