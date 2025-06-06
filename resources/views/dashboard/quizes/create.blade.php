@extends('partials.dashboard.master')
@section('content')
    <!-- begin :: Subheader -->
    <div class="toolbar">

        <div class="container-fluid d-flex flex-stack">

            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">

                <!-- begin :: Title -->
                <h1 class="d-flex align-items-center text-dark fw-bolder fs-3 my-1"><a
                        href="{{ route('dashboard.quizzes.index') }}"
                        class="text-muted text-hover-primary">{{ __('quizzes') }}</a></h1>
                <!-- end   :: Title -->

                <!-- begin :: Separator -->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!-- end   :: Separator -->

                <!-- begin :: Breadcrumb -->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!-- begin :: Item -->
                    <li class="breadcrumb-item text-muted">
                        {{ __('Add new Quiz') }}
                    </li>
                    <!-- end   :: Item -->
                </ul>
                <!-- end   :: Breadcrumb -->

            </div>

        </div>

    </div>
    <!-- end   :: Subheader -->

    <div class="card">
        <!-- begin :: Card body -->
        <div class="card-body p-0">
            <!-- begin :: Form -->
            <form action="{{ route('dashboard.quizzes.store') }}" class="form" method="post" id="submitted-form"
                data-redirection-url="{{ route('dashboard.quizzes.index') }}">
                @csrf
                <!-- begin :: Card header -->
                <div class="card-header d-flex  justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bolder text-dark">{{ __('Add new Quiz') }}</h3>

                    </div>
                    <div class="pe-5 ">
                        <div class="form-check form-switch">
                            <label for="openCourseSwitch">{{ __('Open Quiz') }}</label>
                            <input class="form-check-input" type="checkbox" id="openCourseSwitch" name="open"
                                value="1">
                        </div>
                    </div>
                </div>
                <!-- end   :: Card header -->

                <!-- begin :: Inputs wrapper -->
                <div class="inputs-wrapper">


                    <div class="row mb-8">

                        <!-- begin :: Column -->
                        <div class="col-md-4 fv-row">

                            <label class="fs-5 fw-bold mb-2">{{ __('Title in arabic') }}</label>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name_ar_inp" name="name_ar"
                                    placeholder="example" />
                                <label for="name_ar_inp">{{ __('Enter the quiz title') }}</label>
                            </div>
                            <p class="invalid-feedback" id="name_ar"></p>
                        </div>
                        <!-- begin :: Column -->
                        <div class="col-md-4 fv-row">

                            <label class="fs-5 fw-bold mb-2">{{ __('Title in english') }}</label>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name_en_inp" name="name_en"
                                    placeholder="example" />
                                <label for="name_en_inp">{{ __('Enter the quiz title') }}</label>
                            </div>
                            <p class="invalid-feedback" id="name_en"></p>


                        </div>

                        <!-- end   :: Column -->
                        <div class="col-md-4 fv-row">

                            <label class="fs-5 fw-bold mb-2">{{ __('duration (by minuts)') }}</label>
                            <div class="form-floating">
                                <input type="number" min="1" class="form-control" id="duration_inp" name="duration"
                                    placeholder="example" />
                                <label for="duration_inp">{{ __('Enter the quiz duration') }}</label>
                            </div>
                            <p class="invalid-feedback" id="duration"></p>


                        </div>
                        <!-- end   :: Column -->



                    </div>
                    <div class="row mb-10 align-items-center">
{{-- 
                        <div class="col-md-3 fv-row">

                            <label class="fs-5 fw-bold mb-2">{{ __('Consultaion') }}</label>
                            <select class="form-select" data-control="select2" name="consultaion_id" id="consultaion_id_inp"
                                data-placeholder="{{ __('Choose the consultation') }}"
                                data-dir="{{ isArabic() ? 'rtl' : 'ltr' }}">
                                <option value="" selected></option>
                                @foreach ($Consultaion as $Consultaio)
                                    <option value="{{ $Consultaio->id }}"> {{ $Consultaio->title }} </option>
                                @endforeach
                            </select>
                            <p class="invalid-feedback" id="consultaion_id"></p>


                        </div> --}}

                    </div>
                    <!-- begin :: Row -->
                    <div class="row mb-10">

                        <!-- begin :: Column -->
                        <div class="col-md-6 fv-row">

                            <label class="fs-5 fw-bold mb-2">{{ __('Description in arabic') }}</label>
                            <textarea class="form-control" rows="4" name="description_ar" id="meta_tag_description_ar_inp"></textarea>
                            <p class="text-danger invalid-feedback" id="description_ar"></p>


                        </div>
                        <!-- end   :: Column -->

                        <!-- begin :: Column -->
                        <div class="col-md-6 fv-row">

                            <label class="fs-5 fw-bold mb-2">{{ __('Description in english') }}</label>
                            <textarea class="form-control" rows="4" name="description_en" id="meta_tag_description_en_inp"></textarea>
                            <p class="text-danger invalid-feedback" id="description_en"></p>

                        </div>
                        <!-- end   :: Column -->

                    </div>

                </div>
                <!-- end   :: Inputs wrapper -->

                <!-- begin :: Form footer -->
                <div class="form-footer">

                    <!-- begin :: Submit btn -->
                    <button type="submit" class="btn btn-primary" id="submit-btn">

                        <span class="indicator-label">{{ __('Save') }}</span>

                        <!-- begin :: Indicator -->
                        <span class="indicator-progress">{{ __('Please wait ...') }}
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                        <!-- end   :: Indicator -->

                    </button>
                    <!-- end   :: Submit btn -->

                </div>
                <!-- end   :: Form footer -->
            </form>
            <!-- end   :: Form -->
        </div>
        <!-- end   :: Card body -->
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/dashboard/forms/course/common.js') }}"></script>
@endpush
