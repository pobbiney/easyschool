<style>
    .form-wizard-stepper {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 32px;
        position: relative;
    }

    .form-wizard-stepper::before {
        content: "";
        position: absolute;
        top: 22px;
        left: 40px;
        right: 40px;
        height: 2px;
        background: var(--neutral-200, #e5e7eb);
        z-index: 0;
    }

    .wizard-step-item {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
        cursor: pointer;
    }

    .wizard-step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--white, #fff);
        border: 2px solid var(--neutral-300, #d1d5db);
        color: var(--neutral-500, #6b7280);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .wizard-step-item.active .wizard-step-circle,
    .wizard-step-item.completed .wizard-step-circle {
        background: var(--primary-600, #25A194);
        border-color: var(--primary-600, #25A194);
        color: #fff;
    }

    .wizard-step-item.completed .wizard-step-circle {
        background: var(--success-600, #16a34a);
        border-color: var(--success-600, #16a34a);
    }

    .wizard-step-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--neutral-500, #6b7280);
        line-height: 1.3;
    }

    .wizard-step-item.active .wizard-step-label {
        color: var(--primary-600, #25A194);
    }

    .wizard-step-item.completed .wizard-step-label {
        color: var(--success-600, #16a34a);
    }

    .wizard-step-panel {
        display: none;
    }

    .wizard-step-panel.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .wizard-error-msg {
        color: #dc2626;
        font-size: 13px;
        margin-top: 8px;
        display: none;
    }

    #studentPicturePreview {
        margin-top: 12px;
        display: flex;
        justify-content: center;
    }

    #studentPicturePreview img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        border: 3px solid var(--primary-600, #25A194);
        box-shadow: 0 4px 12px rgba(37, 161, 148, 0.2);
    }

    #studentPicturePreview:empty {
        display: none;
    }

    @media (max-width: 767px) {
        .form-wizard-stepper {
            flex-direction: column;
            align-items: stretch;
        }

        .form-wizard-stepper::before {
            display: none;
        }

        .wizard-step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .wizard-step-circle {
            margin: 0;
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            font-size: 14px;
        }
    }
</style>
