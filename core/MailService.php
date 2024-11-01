<?php

namespace Core;

use Core\AppConfig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use App\Base\Service\ConfigService;

class MailService
{
    public const TEMPLATE_DIRECTORY = 'template/mail';
    private const DEFAULT_CHARSET = 'UTF-8';

    protected bool $use_mail = false;

    private ?PHPMailer $mail;
    private ConfigService $config_service;

    public function __construct(ConfigService $config_service)
    {
        $this->config_service = $config_service;
        $this->use_mail = (bool)$this->config_service->getConfig('config', 'use_mail', false);
        $this->initializeMailer();
    }

    /**
     * 메일 발송
     * @param string $to 받는이 메일
     * @param string $subject 제목
     * @param string $content 내용
     * @param array $options 메일 옵션
     *   - files: 첨부 파일 배열 (['path' => '파일 경로', 'name' => '파일 이름'])
     *   - cc: 참조 메일 주소 (문자열 또는 문자열 배열)
     *   - bcc: 숨은 참조 메일 주소 (문자열 또는 문자열 배열)
     *   - from_name: 발신자 이름 (기본값: 설정된 기본 이름)
     *   - from_mail: 발신자 메일 주소 (기본값: 설정된 기본 메일 주소)
     * @return bool
     */
    public function send(string $to, string $subject, string $content, array $options = []): bool
    {
        // 메일발송 설정되지 않았을 때는 발송하지 않음
        if (!$this->use_mail) {
            throw new \Exception("메일 사용설정이 되어있지 않습니다.", 400);
        }

        if (!$this->mail) {
            return false;
        }

        $from_name = $options['from_name'] ?? $this->getDefaultFromName();
        $from_mail = $options['from_mail'] ?? $this->getDefaultFromMail();

        try {
            $this->prepareMail($to, $from_name, $from_mail, $subject, $content, $options);
            $send_result = $this->mail->send();
            $this->clearMail();

            return $send_result;
        } catch (\Exception $e) {
            error_log("Send Mail Error : " . $e->getMessage());
            return false;
        }
    }

    /**
     * 메일 사용 설정
     * @param bool $use_mail
     * @return void
     */
    public function setUseMail(bool $use_mail): void
    {
        $this->use_mail = $use_mail;
    }

    /**
     * PHPMailer 설정
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int $port
     * @param int $debug_mode
     * @return PHPMailer|null
     */
    public function configureMailer(string $host, string $username, string $password, int $port, int $debug_mode = SMTP::DEBUG_OFF): ?PHPMailer
    {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = $debug_mode;          // Enable verbose debug output
            $mail->isSMTP();                         // Send using SMTP
            $mail->Host = $host;                     // Set the SMTP server to send through
            $mail->Port = $port;                     // TCP port to connect to
            $mail->SMTPAuth = true;               // Enable SMTP authentication
            $mail->Username = $username;             // SMTP username
            $mail->Password = $password;             // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Enable implicit TLS encryption
            $mail->CharSet = self::DEFAULT_CHARSET;  // Set email charset
            $mail->isHTML(true);                     // Set email format to HTML
        } catch (\Exception $e) {
            error_log("configure Mailer Error : " . $e->getMessage());
            return null;
        }

        return $mail;
    }

    /**
     * 메일러 초기화
     */
    private function initializeMailer(): void
    {
        $app_config = AppConfig::getInstance();
        $debug = $app_config->get('APP_DEBUG') ? SMTP::DEBUG_LOWLEVEL : SMTP::DEBUG_OFF;
        $host = $app_config->get('SMTP');
        $port = $app_config->get('SMTP_PORT');
        $username = $app_config->get('SMTP_USERNAME');
        $password = $app_config->get('SMTP_PASSWORD');
        $this->mail = $this->configureMailer($host, $username, $password, $port, $debug);
    }

    /**
     * 기본 발신자 이름 가져오기
     * @return string
     */
    private function getDefaultFromName(): string
    {
        $configs = $this->config_service->getConfigs('config');
        $default_name = $configs['site_title'] ?? AppConfig::getInstance()->get('APP_NAME');
        return isset($configs['mail_name']) && $configs['mail_name'] !== '' ? $configs['mail_name'] : $default_name;
    }

    /**
     * 기본 발신자 메일 가져오기
     * @return string
     */
    private function getDefaultFromMail(): string
    {
        return $this->config_service->getConfig('config', 'mail_name', '');
    }

    /**
     * 메일 준비
     * @param string $to
     * @param string $from_name
     * @param string $from_mail
     * @param string $subject
     * @param string $content
     * @param array $options
     * @return void
     */
    private function prepareMail(string $to, string $from_name, string $from_mail, string $subject, string $content, array $options): void
    {
        $this->mail->addAddress($to);
        $this->mail->setFrom($from_mail, $from_name);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($content);

        if (!empty($options['cc'])) {
            $this->mail->addCC($options['cc']);
        }

        if (!empty($options['bcc'])) {
            $this->mail->addBCC($options['bcc']);
        }

        if (!empty($options['files'])) {
            foreach ($options['files'] as $file) {
                $this->mail->addAttachment($file['path'], $file['name']);
            }
        }
    }

    /**
     * 메일 클리어
     * @return void
     */
    private function clearMail(): void
    {
        $this->mail->clearAddresses();
        $this->mail->clearAttachments();
        $this->mail->clearCustomHeaders();
        $this->mail->clearReplyTos();
        $this->mail->clearAllRecipients();
    }
}
