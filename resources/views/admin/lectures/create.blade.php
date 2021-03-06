@extends('layouts.admin')

@section('content')

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="">الرئيسية </a>
                                </li>
                                <li class="breadcrumb-item"><a href=""> المحاضرات الرئيسية </a>
                                </li>
                                <li class="breadcrumb-item active">إضافة محاضره
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- Basic form layout section start -->
                <section id="basic-form-layouts">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title" id="basic-layout-form"> إضافة محاضره </h4>
                                    <a class="heading-elements-toggle"><i
                                            class="la la-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                @include('admin.includes.alerts.success')
                                @include('admin.includes.alerts.errors')
                                <div class="card-content collapse show">
                                    <div class="card-body">
                                        <form class="form" action="{{route('admin.lectures.store')}}"
                                              method="POST"
                                              enctype="multipart/form-data">
                                            @csrf

                                            <div class="form-body">

                                                <h4 class="form-section"><i class="ft-home"></i> بيانات المحاضره </h4>

                                                @if(get_languages() -> count() > 0)
                                                    @foreach(get_languages() as $index => $lang)
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="projectinput1"> اسم المحاضره
                                                                        - {{__('messages.'.$lang -> abbr)}} </label>
                                                                    <input type="text" value="" id="name"
                                                                           class="form-control"
                                                                           placeholder="  "
                                                                           name="lecture[{{$index}}][name]">
                                                                    @if(!empty(session()->get('errors')['lecture.'.$index.'.name']))
                                                                        <span class="text-danger"> هذا الحقل مطلوب</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label for="projectinput1">وصف المحاضره
                                                                        - {{__('messages.'.$lang -> abbr)}} </label>
                                                                    <textarea class="form-control"
                                                                              placeholder="  "
                                                                              name="lecture[{{$index}}][description]"></textarea>
                                                                    @if(!empty(session()->get('errors')['lecture.'.$index.'.description']))
                                                                        <span class="text-danger"> هذا الحقل مطلوب</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 hidden">
                                                                <div class="form-group">
                                                                    <label for="projectinput1"> أختصار
                                                                        اللغة {{__('messages.'.$lang -> abbr)}} </label>
                                                                    <input type="text" id="abbr"
                                                                           class="form-control"
                                                                           placeholder="  "
                                                                           value="{{$lang -> abbr}}"
                                                                           name="lecture[{{$index}}][abbr]">

                                                                    @if(!empty(session()->get('errors')['lecture.'.$index.'.abbr']))
                                                                        <span class="text-danger"> هذا الحقل مطلوب</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label for="projectinput1">اختر الماده</label>
                                                                    <select name="lecture[0][subject]" class="form-control">
                                                                        <option value="">اختر الماده</option>
                                                                        @if(!empty($subjects) && count($subjects) > 0)
                                                                            @foreach($subjects as $subject)
                                                                                <option value="{{$subject->id}}">{{$subject->subject_name}}</option>
                                                                            @endforeach
                                                                        @else
                                                                            <option value="" disabled>ليس هناك مواد</option>
                                                                        @endif
                                                                    </select>
                                                                    @if(!empty(session()->get('errors')['lecture.' . $index . '.subject']))
                                                                        <span class="text-danger"> هذا الحقل مطلوب</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <label for="projectinput1">اختر المعلم</label>
                                                                    <select name="lecture[0][teacher]" class="form-control">
                                                                        <option value="">اختر المعلم</option>
                                                                        @if(!empty($teachers) && count($teachers) > 0)
                                                                            @foreach($teachers as $teacher)
                                                                                <option value="{{$teacher->id}}">{{$teacher->fullname}}</option>
                                                                            @endforeach
                                                                        @else
                                                                            <option value="" disabled>ليس معلمين</option>
                                                                        @endif
                                                                    </select>
                                                                    @if(!empty(session()->get('errors')['lecture.' . $index . '.teacher']))
                                                                        <span class="text-danger"> هذا الحقل مطلوب</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="form-group mt-1">
                                                                    <input type="checkbox" value="1"
                                                                           name="lecture[0][active]"
                                                                           id="switcheryColor4"
                                                                           class="switchery" data-color="success"
                                                                           checked/>
                                                                    <label for="switcheryColor4"
                                                                           class="card-title ml-1">الحالة</label>

                                                                    @if(!empty(session()->get('errors')['lecture.'.$index.'.active']))
                                                                        <span class="text-danger"> هذا الحقل مطلوب</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                @endif
                                            </div>


                                            <div class="form-actions">
                                                <button type="button" class="btn btn-warning mr-1"
                                                        onclick="history.back();">
                                                    <i class="ft-x"></i> تراجع
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="la la-check-square-o"></i> حفظ
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- // Basic form layout section end -->
            </div>
        </div>
    </div>

@endsection
