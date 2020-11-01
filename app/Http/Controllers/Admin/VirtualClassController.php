<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Groups;
use App\Models\Lecture;
use App\Models\MainCategory;
use App\Models\Major;
use App\Models\StudentClass;
use App\Models\Subjects;
use App\Models\VirtualClassroom;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MacsiDigital\Zoom\Support\Entry;
use MacsiDigital\Zoom\Zoom;
use mikemix\Wiziq;
use Validator;


class VirtualClassController extends Controller
{

    // zoom
    public function createZoom()
    {
        $categories = MainCategory::where('translation_of', 0)->active()->get();
        $majors = Major::all()->where('translation_lang', get_default_lang())->sortByDesc('id');
        $subjects = Subjects::all()->where('translation_lang', get_default_lang())->sortByDesc('id');
        // $groups = Groups::all()->where('translation_lang', get_default_lang())->sortByDesc('id');
        $lectures = Lecture::all()->where('translation_lang', get_default_lang())->sortByDesc('id');

        return view('admin.zoom.create', compact('categories', 'majors', 'subjects', 'lectures'));

    }

    public function storeZOOM(Request $request)
    {
        $zoom = new Entry;
        // $user = new \MacsiDigital\Zoom\User($zoom);
        // $zoom = new Zoom;
        $user = $zoom->user->find('amal_zooky@yahoo.com');
        $meeting = $user->meetings()->create([


            'type' => 2,
            'start_time' => $request->start_time,
            'topic' => 'test create meeting'
        ]);


        $virtualClassroom = new VirtualClassroom;
        $virtualClassroom->title = $request->title;
        $virtualClassroom->group_id = $request->lecture_id;
        $virtualClassroom->subject_id = $request->subject_id;
        $virtualClassroom->start_time = $request->start_time;
        $virtualClassroom->join_url = $meeting->join_url;
        $virtualClassroom->user_id = auth()->id();
        $virtualClassroom->save();

        // session()->flash('success', __('dashboard.statuses.virtual_classroom_created'));
        // if(auth()->user()->hasRole('teacher')){
        //     return response()->json(["redir"=>route('dashboard.home')]);
        // }

        // return response()->json(["redir"=>route('virtual-classroom.index')]);
        echo "Link of meeting is: $meeting->join_url";
    }

    // index ???
    public function index()
    {
        Bigbluebutton::create([
            'meetingID' => 'tamku',
            'meetingName' => 'test meeting',
            'attendeePW' => 'attendee',
            'moderatorPW' => 'moderator',
            'bbb-recording-ready-url' => 'https://example.com/api/v1/recording_status',
        ]);
        // // dd(\Bigbluebutton::isConnect()); //default
        // // dd(\Bigbluebutton::server('server1')->isConnect()); //for specific server
        // dd(bigbluebutton()->isConnect()); //using helper method
    }

    // wiziq
    public function createWiziq()
    {
        $categories = MainCategory::where('translation_of', 0)->active()->get();
        $majors = Major::all()->where('translation_lang', get_default_lang())->sortByDesc('id');
        $subjects = Subjects::all()->where('translation_lang', get_default_lang())->sortByDesc('id');
        $lectures = Lecture::all()->where('translation_lang', get_default_lang())->sortByDesc('id');

        return view('admin.wiziq.create', compact('categories', 'majors', 'subjects', 'lectures'));

    }

    private function wiziqConfig()
    {
        $auth = new Wiziq\API\Auth(config('wiziq.secret_Key'), config('wiziq.access_key'));
        $gateway = new Wiziq\API\Gateway($auth);
        $api = new Wiziq\API\ClassroomApi($gateway);

        return $api;
    }

    private function createWiziqClass($request)
    {
        $lecture = Lecture::findOrFail($request->lecture_id);
        $teacher = $lecture->teacher;
        $classTitle = $request->title;

//        $startTime = '2020-10-29 23:37:7';
//        $classTitle = 'test class title';
//        $teacherId = 1;
//        $teacherName = 'Ahmed Abdullah';
//        $attendeeLimit = 10; // max in free us 10
//        $createRecording = false; // false non recording, true recording

        $api = $this->wiziqConfig();
        $startTimeWiziq = Carbon::parse($request->start_time)->format("m/d/Y H:i");

        try {
            $classroom = Wiziq\Entity\Classroom::build($classTitle, new DateTime($startTimeWiziq))
                ->withPresenter($teacher->id, $teacher->fullname)
                ->withDuration(300)
                ->withAttendeeLimit($request->attendee_limit)
                ->withCreateRecording(!empty($request->create_recording))
                ->withTimeZone('Asia/Jerusalem');

            $response = $api->create($classroom);
            return $response;
            // printf('Class %s created: %s', $classroom, var_export($response, true));
        } catch (Wiziq\Common\Api\Exception\CallException $e) {
            die($e->getMessage()); // change to return false
        } catch (Wiziq\Common\Http\Exception\InvalidResponseException $e) {
            die($e->getMessage()); // change to return false
        }
    }

    private function addAttendees($classID, $students)
    {
        $api = $this->wiziqConfig();

        try {
            $classroomId = $classID;

            $attendees = Wiziq\Entity\Attendees::build();

            foreach ($students as $student) {
                $attendees = $attendees->add($student->id, $student->fullname, 'ar-SA');
            }

            $response = $api->addAttendeesToClass($classroomId, $attendees);
            return $response;
            // printf('Attendees to class %d added: %s', $classroomId, var_export($response, true));
        } catch (Wiziq\Common\Api\Exception\CallException $e) {
            die($e->getMessage());
        } catch (Wiziq\Common\Http\Exception\InvalidResponseException $e) {
            die($e->getMessage());
        }
    }

    public function storeWiziq(Request $request)
    {
        $virtualClassroom = new VirtualClassroom;
        $virtualClassroom->title = $request->title;
        $virtualClassroom->subject_id = $request->subject_id;
        $virtualClassroom->group_id = $request->lecture_id;
        $virtualClassroom->start_time = $request->start_time;
        $virtualClassroom->user_id = auth()->id();

        if ($wiziqResponse = $this->createWiziqClass($request)) {
            $virtualClassroom->class_id = $wiziqResponse['class_id'];
            $virtualClassroom->recording_url = $wiziqResponse['recording_url'];
//            $virtualClassroom->presenter_url = $wiziqResponse['presenter_url'];
            $virtualClassroom->join_url = $wiziqResponse['presenter_url'];

            if ($virtualClassroom->save()) {
                $students = DB::table('student_subjects')->select('vendors.id', 'vendors.fullname')
                    ->join('vendors', 'student_subjects.student_id', '=', 'vendors.id')
                    ->where('subject_id', '=', 10)->get();
                $wiziqResponse = $this->addAttendees($wiziqResponse['class_id'], $students);

                if (!empty($wiziqResponse)) {
                    $studentClassData = [];
                    foreach ($wiziqResponse as $user) {
                        $studentClassData[] = [
                            "virtual_classroom_id" => $virtualClassroom->id,
                            "url" => $user["url"],
                            "student_id" => $user["id"],
                        ];
                    }
                    StudentClass::insert($studentClassData);
                }
            }
        }

        session()->flash('success', 'تم انشاء الدرس');
        return redirect('admin.virtualclass.wiziq.create');
    }
}
