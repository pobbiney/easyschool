@php
    $student = $student ?? null;
    $docs = $docs ?? collect();
    $submitLabel = $submitLabel ?? 'Register Student';
    $step4Description = $step4Description ?? 'Attach any supporting documents, then submit to register the student.';
    $studentRecordId = $studentRecordId ?? old('student_record_id', $student?->id ?? '');
@endphp

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="studentWizardForm" class="mt-24">
    @csrf
    <input type="hidden" name="student_record_id" id="studentRecordId" value="{{ $studentRecordId }}">
    <input type="hidden" name="current_step" id="currentStepInput" value="1">

    <div class="shadow-1 radius-12 bg-base p-24 mb-24">
        <div class="form-wizard-stepper" id="wizardStepper">
            <div class="wizard-step-item active" data-step="1">
                <div class="wizard-step-circle"><i class="ri-user-3-line"></i></div>
                <div class="wizard-step-label">Personal Info</div>
            </div>
            <div class="wizard-step-item" data-step="2">
                <div class="wizard-step-circle"><i class="ri-parent-line"></i></div>
                <div class="wizard-step-label">Parent & Guardian</div>
            </div>
            <div class="wizard-step-item" data-step="3">
                <div class="wizard-step-circle"><i class="ri-heart-pulse-line"></i></div>
                <div class="wizard-step-label">Medical & Other</div>
            </div>
            <div class="wizard-step-item" data-step="4">
                <div class="wizard-step-circle"><i class="ri-file-upload-line"></i></div>
                <div class="wizard-step-label">Documents</div>
            </div>
        </div>

        <div class="wizard-step-panel active" data-step="1">
            <div class="card border-0 shadow-none">
                <div class="card-header border-bottom bg-base py-16 px-0">
                    <h6 class="text-lg fw-semibold mb-0">Step 1 — Personal Information</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">Enter the student's basic details and photo.</p>
                    <div id="studentPicturePreview">
                        @if(!empty($student?->picture))
                            <img src="{{ asset($student->picture) }}" alt="Student photo">
                        @endif
                    </div>
                </div>
                <div class="card-body px-0 pt-20">
                    @include('student.partials._section-personal')
                    <div class="wizard-error-msg" id="step1Error">Please fill in all required fields before continuing.</div>
                </div>
            </div>
        </div>

        <div class="wizard-step-panel" data-step="2">
            <div class="card border-0 shadow-none">
                <div class="card-header border-bottom bg-base py-16 px-0">
                    <h6 class="text-lg fw-semibold mb-0">Step 2 — Parent & Guardian Information</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">Add father, mother, and guardian contact details.</p>
                </div>
                <div class="card-body px-0 pt-20">
                    @include('student.partials._section-parent')
                    <div class="wizard-error-msg" id="step2Error">Please fill in all parent and guardian fields before continuing.</div>
                </div>
            </div>
        </div>

        <div class="wizard-step-panel" data-step="3">
            <div class="card border-0 shadow-none">
                <div class="card-header border-bottom bg-base py-16 px-0">
                    <h6 class="text-lg fw-semibold mb-0">Step 3 — Medical & Other Information</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">Add medical details, addresses, and any other notes.</p>
                </div>
                <div class="card-body px-0 pt-20">
                    @include('student.partials._section-other')
                </div>
            </div>
        </div>

        <div class="wizard-step-panel" data-step="4">
            <div class="card border-0 shadow-none">
                <div class="card-header border-bottom bg-base py-16 px-0">
                    <h6 class="text-lg fw-semibold mb-0">Step 4 — Upload Documents</h6>
                    <p class="text-sm text-secondary-light mb-0 mt-4">{{ $step4Description }}</p>
                </div>
                <div class="card-body px-0 pt-20">
                    @include('student.partials._section-documents')
                    @if($docs->isNotEmpty())
                        <div class="table-responsive mt-16">
                            <h6 class="text-md fw-semibold mb-12">Existing Documents</h6>
                            <table class="table bordered-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Document Name</th>
                                        <th>File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($docs as $doc)
                                        <tr>
                                            <td>{{ $doc->doc_name }}</td>
                                            <td>
                                                <a href="{{ asset($doc->document_path) }}" target="_blank" class="text-primary-600">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between gap-3">
        <a href="{{ route('list-students') }}" class="btn btn-danger-600 border border-danger-600 text-md px-28 py-12 radius-8">Cancel</a>
        <div class="d-flex gap-3 flex-wrap justify-content-end">
            <button type="button" id="wizardPrevBtn" class="border border-neutral-400 bg-hover-neutral-200 text-secondary-light text-md px-28 py-11 radius-8" style="display:none;">
                <i class="ri-arrow-left-line"></i> Previous
            </button>
            <button type="button" id="wizardSaveContinueBtn" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">
                <i class="ri-save-line"></i> Save & Continue Later
            </button>
            <button type="submit" id="wizardSubmitBtn" class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8" style="display:none;">
                <i class="ri-check-line"></i> {{ $submitLabel }}
            </button>
        </div>
    </div>
</form>
