<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilMailOptionsFormGUI
 */
class ilMailOptionsFormGUI extends ilPropertyFormGUI
{
    protected object $parentGui;

    public function __construct(protected ilMailOptions $options, object $parentGui, protected string $positiveCmd)
    {
        if (!method_exists($parentGui, 'executeCommand')) {
            throw new InvalidArgumentException(sprintf(
                'Parameter $parentGui must be ilCtrlInterface enabled by implementing executeCommand(), %s given.',
                $parentGui::class
            ));
        }

        parent::__construct();
        $this->parentGui = $parentGui;

        $this->init();
    }

    protected function init(): void
    {
        $this->setTitle($this->lng->txt('mail_settings'));
        $this->setFormAction($this->ctrl->getFormAction($this->parentGui, $this->positiveCmd));

        if ($this->settings->get('usr_settings_hide_mail_incoming_mail', '0') !== '1') {
            $incoming_mail_gui = new ilIncomingMailInputGUI(
                $this->lng->txt('mail_incoming'),
                'incoming_type',
                false
            );
            $this->addItem($incoming_mail_gui);
        }

        $ta = new ilTextAreaInputGUI($this->lng->txt('signature'), 'signature');
        $ta->setRows(10);
        $ta->setCols(60);
        $this->addItem($ta);

        if ($this->settings->get('mail_notification', '0')) {
            $cb = new ilCheckboxInputGUI(
                $this->lng->txt('cron_mail_notification'),
                'cronjob_notification'
            );
            $cb->setInfo($this->lng->txt('mail_cronjob_notification_info'));
            $cb->setValue('1');
            $this->addItem($cb);
        }

        $absence = new ilCheckboxInputGUI($this->lng->txt('absence_status'), 'absence_status');
        $absence->setInfo($this->lng->txt('absence_status_info'));
        $absence->setValue("1");
        $this->lng->loadLanguageModule('dateplaner');
        $duration = new ilDateDurationInputGUI($this->lng->txt('absence_duration'), 'absence_duration');
        $duration->setRequired(true);
        $duration->setStartText($this->lng->txt('absent_from'));
        $duration->setEndText($this->lng->txt('absent_until'));
        $duration->setShowTime(true);
        $auto_responder_subject = new ilTextInputGUI($this->lng->txt('absence_auto_responder_subject'), 'absence_auto_responder_subject');
        $auto_responder_subject->setMaxLength(200);
        $auto_responder_subject->setRequired(true);
        $auto_responder_body = new ilTextAreaInputGUI($this->lng->txt('absence_auto_responder_body'), 'absence_auto_responder_body');
        $auto_responder_body->setInfo($this->lng->txt('absence_auto_responder_body_info'));
        $auto_responder_body->setRequired(true);
        $auto_responder_body->setCols(60);
        $auto_responder_body->setRows(10);
        $absence->addSubItem($duration);
        $absence->addSubItem($auto_responder_subject);
        $absence->addSubItem($auto_responder_body);
        $this->addItem($absence);

        $this->addCommandButton($this->positiveCmd, $this->lng->txt('save'));
    }

    public function save(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        if (
            $this->settings->get('usr_settings_hide_mail_incoming_mail', '0') !== '1' &&
            $this->settings->get('usr_settings_disable_mail_incoming_mail', '0') !== '1'
        ) {
            $incoming_type = (int) $this->getInput('incoming_type');

            $mail_address_option = $this->options->getEmailAddressMode();
            switch ($incoming_type) {
                case ilMailOptions::INCOMING_EMAIL:
                    $mail_address_option = (int) $this->getInput('mail_address_option');
                    break;

                case ilMailOptions::INCOMING_BOTH:
                    $mail_address_option = (int) $this->getInput('mail_address_option_both');
                    break;
            }
        } else {
            $incoming_type = $this->options->getIncomingType();
            $mail_address_option = $this->options->getEmailAddressMode();
        }

        $absence_duration = $this->getItemByPostVar('absence_duration');

        $this->options->setAbsenceStatus((bool) $this->getInput('absence_status'));
        if ($absence_duration && $absence_duration->getStart() && $absence_duration->getEnd()) {
            $this->options->setAbsentFrom($absence_duration->getStart()->get(IL_CAL_UNIX));
            $this->options->setAbsentUntil($absence_duration->getEnd()->get(IL_CAL_UNIX));
        }
        $this->options->setAbsenceAutoResponderSubject($this->getInput('absence_auto_responder_subject'));
        $this->options->setAbsenceAutoResponderBody($this->getInput('absence_auto_responder_body'));

        $this->options->setSignature($this->getInput('signature'));
        $this->options->setIsCronJobNotificationStatus((bool) $this->getInput('cronjob_notification'));
        $this->options->setIncomingType($incoming_type);
        $this->options->setEmailAddressMode($mail_address_option);

        $this->options->updateOptions();

        return true;
    }

    public function populate(): void
    {
        $data = [
            'signature' => $this->options->getSignature(),
            'cronjob_notification' => $this->options->isCronJobNotificationEnabled(),
            'absence_status' => $this->options->getAbsenceStatus(),
            'absence_duration' => [
                'start' => (new ilDateTime(($this->options->getAbsentFrom() ?: time()), IL_CAL_UNIX))->get(IL_CAL_DATETIME),
                'end' => (new ilDateTime(($this->options->getAbsentUntil() ?: time() + 8640), IL_CAL_UNIX))->get(IL_CAL_DATETIME),
            ],
            'absence_auto_responder_subject' => $this->options->getAbsenceAutoResponderSubject(),
            'absence_auto_responder_body' => $this->options->getAbsenceAutoResponderBody(),
        ];

        if ($this->settings->get('usr_settings_hide_mail_incoming_mail', '0') !== '1') {
            $data['incoming_type'] = $this->options->getIncomingType();

            $mail_address_option = $this->options->getEmailAddressMode();

            $data['mail_address_option'] = $mail_address_option;
            $data['mail_address_option_both'] = $mail_address_option;
        }

        $this->setValuesByArray($data);
    }
}
