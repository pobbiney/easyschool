<script>
    let currentStep = 1;
    const totalSteps = 4;
    let docCounter = {{ $docCounterStart ?? 0 }};

    function showStep(step) {
        currentStep = step;

        $('.wizard-step-panel').removeClass('active');
        $('.wizard-step-panel[data-step="' + step + '"]').addClass('active');

        $('.wizard-step-item').each(function () {
            let itemStep = $(this).data('step');
            $(this).removeClass('active completed');

            if (itemStep < step) {
                $(this).addClass('completed');
                $(this).find('.wizard-step-circle').html('<i class="ri-check-line"></i>');
            } else if (itemStep === step) {
                $(this).addClass('active');
            }
        });

        restoreStepIcons(step);

        $('#wizardPrevBtn').toggle(step > 1);
        $('#wizardSaveContinueBtn').toggle(step < totalSteps);
        $('#wizardSubmitBtn').toggle(step === totalSteps);
        $('#currentStepInput').val(step);

        $('html, body').animate({ scrollTop: $('#studentWizardForm').offset().top - 80 }, 200);
    }

    function restoreStepIcons(current) {
        const icons = ['ri-user-3-line', 'ri-parent-line', 'ri-heart-pulse-line', 'ri-file-upload-line'];
        $('.wizard-step-item').each(function () {
            let itemStep = $(this).data('step');
            if (itemStep >= current) {
                $(this).find('.wizard-step-circle').html('<i class="' + icons[itemStep - 1] + '"></i>');
            }
        });
    }

    function validateStep(step) {
        let isValid = true;
        let stepPanel = $('.wizard-step-panel[data-step="' + step + '"]');

        if (step === 1) {
            stepPanel.find('.wizard-required').each(function () {
                $(this).removeClass('border-danger-600');

                if ($(this).is('select')) {
                    if (!$(this).val()) {
                        $(this).addClass('border-danger-600');
                        isValid = false;
                    }
                } else if ($(this).val().trim() === '') {
                    $(this).addClass('border-danger-600');
                    isValid = false;
                }
            });

            $('#step1Error').toggle(!isValid);
            return isValid;
        }

        if (step === 2) {
            stepPanel.find('.wizard-step2-required').each(function () {
                $(this).removeClass('border-danger-600');

                if ($(this).is('select')) {
                    if (!$(this).val()) {
                        $(this).addClass('border-danger-600');
                        isValid = false;
                    }
                } else if ($(this).val().trim() === '') {
                    $(this).addClass('border-danger-600');
                    isValid = false;
                }
            });

            $('#step2Error').toggle(!isValid);
            return isValid;
        }

        return true;
    }

    function saveAndContinue() {
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep >= totalSteps) {
            return;
        }

        let form = document.getElementById('studentWizardForm');
        let formData = new FormData(form);
        formData.set('current_step', currentStep);

        $('#wizardSaveContinueBtn').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Saving...');

        fetch('{{ $draftSaveUrl }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        })
        .then(function (data) {
            if (data.student_record_id) {
                $('#studentRecordId').val(data.student_record_id);
            }

            if (typeof showAppToast === 'function') {
                showAppToast('success', data.message || 'Progress saved. You can continue later.');
            }

            showStep(currentStep + 1);
        })
        .catch(function (error) {
            let message = 'Could not save progress. Please try again.';

            if (error.message) {
                message = error.message;
            }

            if (error.errors) {
                message = Object.values(error.errors).flat().join(' ');
            }

            if (typeof showAppToast === 'function') {
                showAppToast('error', message);
            } else {
                alert(message);
            }
        })
        .finally(function () {
            $('#wizardSaveContinueBtn').prop('disabled', false).html('<i class="ri-save-line"></i> Save & Continue Later');
        });
    }

    $('#wizardSaveContinueBtn').click(function () {
        saveAndContinue();
    });

    $('#wizardPrevBtn').click(function () {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });

    $('.wizard-step-item').click(function () {
        let targetStep = $(this).data('step');

        if (targetStep < currentStep) {
            showStep(targetStep);
            return;
        }

        if (targetStep > currentStep) {
            return;
        }
    });

    $('#studentPicture').change(function () {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#studentPicturePreview').html('<img src="' + e.target.result + '" alt="Student photo preview">');
            };
            reader.readAsDataURL(file);
        } else if (!$('#studentPicturePreview img').attr('src')) {
            $('#studentPicturePreview').empty();
        }
    });

    $('#addDocument').click(function () {
        let docName = $('#docName').val();
        let fileInput = $('#documentFile')[0];
        let file = fileInput.files[0];

        if (docName === '') {
            showAppToast('error', 'Enter document name');
            return;
        }

        if (!file) {
            showAppToast('error', 'Choose a file to upload');
            return;
        }

        let row = `
            <tr id="doc_row_${docCounter}">
                <td>${docName}</td>
                <td>${file.name}</td>
                <td><button type="button" class="btn btn-danger btn-sm removeDoc" data-id="${docCounter}">Remove</button></td>
            </tr>
        `;

        $('#documentTable tbody').append(row);

        $('#documentContainer').append(`
            <div id="doc_hidden_${docCounter}" style="display:none">
                <input type="hidden" name="documents[${docCounter}][doc_name]" value="${docName}">
                <input type="file" name="documents[${docCounter}][document]" class="realFileInput">
            </div>
        `);

        let realInput = $('#doc_hidden_' + docCounter + ' .realFileInput')[0];
        const dt = new DataTransfer();
        dt.items.add(file);
        realInput.files = dt.files;

        $('#docName').val('');
        $('#documentFile').val('');
        docCounter++;
    });

    $(document).on('click', '.removeDoc', function () {
        let id = $(this).data('id');
        $('#doc_row_' + id).remove();
        $('#doc_hidden_' + id).remove();
    });

    showStep(1);
</script>
