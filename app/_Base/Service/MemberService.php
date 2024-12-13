<?php

namespace App\Base\Service;

use API\Exceptions\HttpNotFoundException;
use Core\Database\Db;
use Exception;
use Slim\Http\ServerRequest as Request;

class MemberService
{
    public const TABLE_NAME = 'member';
    public const MEMO_TABLE_NAME = 'member_memo';
    public const DIRECTORY = 'member';
    public const IMAGE_WIDTH = 60;
    public const IMAGE_HEIGHT = 60;

    public string $table;
    public string $memo_table;

    private Request $request;
    private ConfigService $config_service;

    public function __construct(
        Request $request,
        ConfigService $config_service
    ) {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->memo_table = $_ENV['DB_PREFIX'] . self::MEMO_TABLE_NAME;

        $this->request = $request;
        $this->config_service = $config_service;
    }

    // ========================================
    // Public Methods
    // ========================================

    /**
     * 회원 목록정보 조회
     * @param array $params 조회조건
     * @return array
     */
    public function getMembers(array $params): array
    {
        $members = $this->fetchMembers($params);

        if (!$members) {
            return [];
        }

        foreach ($members as &$member) {
            $member['status'] = $this->getMemberStatus($member);
        }

        return $members;
    }

    /**
     * 회원 상태 조회
     * @param array $member 회원정보
     * @return string
     */
    public function getMemberStatus(array $member): string
    {
        if ($member['mb_leave_date']) {
            return 'leave';
        }
        if ($member['mb_intercept_date']) {
            return 'intercept';
        }
        return 'normal';
    }

    /**
     * 회원 정보 조회
     * @param string $mb_id 회원아이디
     * @return array
     */
    public function getMember(string $mb_id): array
    {
        if (isHashedMemberId($mb_id)) {
            $member = $this->fetchMemberByHash($mb_id);
        } else {
            $member = $this->fetchMemberById($mb_id);
        }

        if (!$member) {
            throw new HttpNotFoundException($this->request, '회원정보가 존재하지 않습니다.');
        }

        return $member;
    }

    /**
     * 회원메모 목록 조회
     * @param string $mb_id 회원아이디
     * @return array
     */
    public function getMemos(string $mb_id): array
    {
        $memos = $this->fetchMemos($mb_id);
        if (!$memos) {
            return [];
        }

        foreach ($memos as &$memo) {
            $memo['admin'] = $this->fetchMemberById($memo['author_mb_id']);
        }


        return $memos;
    }

    /**
     * 회원메모 조회
     * @param int $memo_id 메모번호
     * @return array
     */
    public function getMemo(int $memo_id): array
    {
        $memo = $this->fetchMemo($memo_id);
        if (!$memo) {
            throw new HttpNotFoundException($this->request, '메모정보가 존재하지 않습니다.');
        }

        return $memo;
    }

    /**
     * 회원가입 처리
     * @param array $data 회원가입 데이터
     * @return int 회원번호
     * @throws Exception 회원가입 실패시 Exception 발생
     */
    public function createMember(array $data): int
    {
        if ($this->fetchMemberById($data['mb_id'])) {
            throw new Exception('이미 사용중인 회원아이디 입니다.', 409);
        }
        if ($this->existsMemberByNick($data['mb_nick'], $data['mb_id'])) {
            throw new Exception('이미 사용중인 닉네임 입니다.', 409);
        }
        if ($this->existsMemberByEmail($data['mb_email'], $data['mb_id'])) {
            throw new Exception('이미 사용중인 이메일 입니다.', 409);
        }
        $recommend_enabled = $this->config_service->getConfig('member', 'recommend_enabled', false);
        if ($recommend_enabled && isset($data['mb_recommend']) && $data['mb_recommend']) {
            if (!$this->fetchMemberById($data['mb_recommend'])) {
                throw new Exception('추천인이 존재하지 않습니다.', 404);
            }
        }

        return $this->insert($data);
    }

    /**
     * 회원정보 갱신 처리
     * @param string $mb_id 회원아이디
     * @param array $data 갱신 데이터
     * @return void
     * @throws Exception 갱신 실패시 Exception 발생
     */
    public function updateMember(array $member, array $data): void
    {
        if (
            $member['mb_nick'] !== $data['mb_nick']
            && $this->existsMemberByNick($data['mb_nick'], $member['mb_id'])
        ) {
            throw new Exception('이미 사용중인 닉네임 입니다.', 409);
        }
        if (
            $member['mb_email'] !== $data['mb_email']
            && $this->existsMemberByEmail($data['mb_email'], $member['mb_id'])
        ) {
            throw new Exception('이미 사용중인 이메일 입니다.', 409);
        }

        $this->update($member['mb_id'], $data);
    }

    /**
     * 회원탈퇴
     * - 실제로 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
     * @param array $member
     * @return void
     * @throws Exception
     */
    public function leaveMember(array $member)
    {
        $update_data = [
            'mb_leave_date' => date('Ymd'),
            'mb_certify' => '',
            'mb_adult' => 0,
            'mb_dupinfo' => ''
        ];
        $this->updateMember($member['mb_id'], $update_data);

        // @todo 메모 추가 처리

        // Hook - 회원탈퇴
        // run_event('member_leave', $member);

        //소셜로그인 해제
        // if (function_exists('social_member_link_delete')) {
        //     social_member_link_delete($member['mb_id']);
        // }
    }

    /**
     * 회원정보 조회 검증
     * @param array $member 회원정보
     * @param array $login_member 로그인 회원정보
     * @return void
     * @throws Exception 조회 실패시 Exception 발생
     */
    public function verifyMemberProfile(array $member, array $login_member): void
    {
        if (!$member) {
            throw new Exception('회원정보가 존재하지 않습니다.', 404);
        }
        if ($login_member['mb_id'] != $member['mb_id']) {
            if (!$login_member['mb_open']) {
                throw new Exception('자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.', 403);
            }
            if (!$member['mb_open']) {
                throw new Exception('해당 회원은 정보공개를 하지 않았습니다.', 403);
            }
        }
    }

    /**
     * 인증 이메일 변경정보 검증
     * @param array|bool $member 회원정보
     * @param object $data 변경정보
     * @return void
     * @throws Exception 변경정보 검증 실패시 Exception 발생
     */
    public function verifyEmailCertification($member, object $data): void
    {
        if (!$member) {
            throw new Exception('회원정보가 존재하지 않습니다.', 404);
        }
        if (!password_verify($data->password, $member['mb_password'])) {
            throw new Exception('비밀번호가 일치하지 않습니다.', 403);
        }
        if (substr($member['mb_email_certify'], 0, 1) != '0') {
            throw new Exception('이미 메일인증 하신 회원입니다.', 409);
        }
        if ($this->existsMemberByEmail($data->email, $member['mb_id'])) {
            throw new Exception('이미 사용중인 이메일 입니다.', 409);
        }
    }

    /**
     * 비밀번호 변경메일 발송 검증
     * @param string $email 이메일
     * @return array
     * @throws Exception 검증 실패시 Exception 발생
     */
    public function verifyPasswordResetEmail(string $email): array
    {
        $members = $this->fetchAllMemberByEmail($email);
        $count = count($members);

        if ($count > 1) {
            throw new Exception('동일한 메일주소가 2개 이상 존재합니다. 관리자에게 문의하여 주십시오.', 409);
        }

        if ($count == 0) {
            throw new Exception('입력한 정보로 등록된 회원을 찾을 수 없습니다.', 404);
        }

        return $members[0];
    }

    public function deleteMember(array $member): void
    {
        // 이미 삭제된 회원은 제외
        if ($member['mb_leave_date']) {
            return;
        }
        // 회원정보 빈 값으로 업데이트
        $this->update($member['mb_id'], [
            'mb_password' => '',
            'mb_level' => 1,
            'mb_email' => '',
            'mb_leave_date' => date('Ymd'),
        ]);
        // @todo 메모 추가 필요
        // @todo
        // 추천 포인트 반환
        // 포인트 테이블에서 삭제
        // 그룹접근가능 삭제
        // 쪽지 삭제
        // 스크랩 삭제
        // 관리권한 삭제
        // 그룹관리자인 경우 그룹관리자를 공백으로
        // 게시판관리자인 경우 게시판관리자를 공백으로
        // 소셜로그인에서 삭제 또는 해제
        // 프로필 이미지 삭제
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 회원 전체 카운트 조회
     * @param array|null $params 조회조건
     * @return int
     */
    public function fetchMembersTotalCount(?array $params = []): int
    {
        $wheres = [];
        $values = [];

        $this->addSearchConditions($wheres, $values, $params);
        $sql_where = Db::buildWhere($wheres);

        $query = "SELECT COUNT(*)
                    FROM {$this->table} AS member
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * 회원 목록 조회
     * @param array|null $params 조회조건
     * @return array|false
     */
    public function fetchMembers(?array $params = []): array
    {
        $wheres = [];
        $values = [];
        
        $this->addSearchConditions($wheres, $values, $params);
        $sql_where = Db::buildWhere($wheres);

        $sql_limit = '';
        if (isset($params['offset']) && isset($params['limit'])) {
            $values['offset'] = $params['offset'];
            $values['limit'] = $params['limit'];
            $sql_limit = 'LIMIT :offset, :limit';
        }

        $query = "SELECT member.*, COUNT(memo.id) AS memo_count
                    FROM {$this->table} AS member
                    LEFT JOIN new_member_memo AS memo ON member.mb_id = memo.mb_id
                    {$sql_where}
                    GROUP BY member.mb_id
                    ORDER BY member.created_at DESC, member.mb_no DESC
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 이메일로 회원정보 목록 조회
     * @param string $mb_email 이메일
     * @return array|false
     */
    public function fetchAllMemberByEmail(string $mb_email)
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_email = :mb_email";

        $stmt = Db::getInstance()->run($query, ['mb_email' => $mb_email]);

        return $stmt->fetchAll();
    }

    /**
     * 회원정보 조회
     * @param string $mb_id 회원아이디
     * @return array|false
     */
    public function fetchMemberById(?string $mb_id = null)
    {
        if (!$mb_id) {
            return false;
        }

        static $cache = [];
        if (isset($cache[$mb_id])) {
            return $cache[$mb_id];
        }

        $query = "SELECT * FROM `{$this->table}` WHERE mb_id = :mb_id";
        $stmt = Db::getInstance()->run($query, ['mb_id' => $mb_id]);
        $cache[$mb_id] = $stmt->fetch();
        return $cache[$mb_id];
    }

    /**
     * 회원메모 목록 조회
     * @param string $mb_id 회원아이디
     * @return array|false
     */
    public function fetchMemos(string $mb_id): array
    {
        $query = "SELECT * FROM {$this->memo_table} WHERE mb_id = :mb_id ORDER BY id DESC";
        return Db::getInstance()->run($query, ['mb_id' => $mb_id])->fetchAll();
    }

    /**
     * 회원메모 조회
     * @param int $memo_id 메모번호
     * @return array|false
     */
    public function fetchMemo(int $memo_id)
    {
        $query = "SELECT * FROM {$this->memo_table} WHERE id = :id";
        return Db::getInstance()->run($query, ['id' => $memo_id])->fetch();
    }

    /**
     * 회원정보를 해시값으로 조회
     * @param string $hash 회원아이디 해시값
     * @return array|false
     */
    public function fetchMemberByHash(string $hash)
    {
        static $cache = [];
        if (isset($cache[$hash])) {
            return $cache[$hash];
        }

        $query = "SELECT * FROM `{$this->table}` WHERE mb_id_hash = :mb_id_hash";
        $stmt = Db::getInstance()->run($query, ['mb_id_hash' => $hash]);
        $cache[$hash] = $stmt->fetch();
        return $cache[$hash];
    }

    /**
     * 회원정보 조회 (회원 레벨)
     * @param int $mb_level 회원레벨
     * @return array|false
     */
    public function fetchMemberByLevel(int $mb_level)
    {
        $query = "SELECT mb_id, mb_name, mb_nick FROM `{$this->table}` WHERE mb_level = :mb_level";
        $stmt = Db::getInstance()->run($query, ['mb_level' => $mb_level]);

        return $stmt->fetchAll();
    }

    /**
     * 닉네임 중복여부 확인
     * @param string $mb_nick 닉네임
     * @param string $mb_id 회원아이디
     * @return bool
     */
    public function existsMemberByNick(string $mb_nick, string $mb_id): bool
    {
        $query = "SELECT COUNT(*) as cnt
                    FROM {$this->table}
                    WHERE mb_nick = :mb_nick
                    AND mb_id <> :mb_id";

        $stmt = Db::getInstance()->run($query, [
            'mb_nick' => $mb_nick,
            'mb_id' => $mb_id
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * 회원아이디 중복여부 확인
     * @param string $mb_id  회원아이디
     * @return bool
     */
    public function existsMemberById(string $mb_id): bool
    {
        $query = "SELECT EXISTS(
            SELECT 1 FROM `{$this->table}`
            WHERE mb_id = :mb_id
        )";

        $stmt = Db::getInstance()->run($query, ['mb_id' => $mb_id]);
        return $stmt->fetchColumn() == 1;
    }

    /**
     * 이메일 중복여부 확인
     * @param string $mb_email 이메일
     * @param string $mb_id 회원아이디
     * @return bool
     */
    public function existsMemberByEmail(string $mb_email, string $mb_id): bool
    {
        $query = "SELECT COUNT(*) as cnt
                    FROM {$this->table}
                    WHERE mb_email = :mb_email
                    AND mb_id <> :mb_id";

        $stmt = Db::getInstance()->run($query, [
            'mb_email' => $mb_email,
            'mb_id' => $mb_id
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * 회원가입 처리
     * @param array $data 회원가입 데이터
     * @return string|false 회원 테이블 mb_no 번호
     */
    public function insert(array $data): int
    {
        $insert_id = Db::getInstance()->insert($this->table, $data);

        return $insert_id;
    }

    /**
     * 회원메모 추가
     * @param array $data 메모 데이터
     * @return int 메모번호
     */
    public function insertMemo(array $data): int
    {
        $insert_id = Db::getInstance()->insert($this->memo_table, $data);

        return $insert_id;
    }

    /**
     * 회원정보 수정 처리
     * @param string $mb_id 회원아이디
     * @param array $data 수정할 데이터
     * @return int 수정된 행 갯수
     */
    public function update(string $mb_id, array $data): int
    {
        $update_count = Db::getInstance()->update($this->table, $data, ['mb_id' => $mb_id]);

        return $update_count;
    }

    /**
     * 새로운 암호화 방식으로 비밀번호 업데이트
     * @param string $mb_id 회원아이디
     * @param string $password 새로운 비밀번호
     * @return bool
     */
    public function updatePasswordRehash(string $mb_id, string $password): bool
    {
        $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_rows = $this->update($mb_id, ['mb_password' => $new_hashed_password]);
        return $update_rows > 0;
    }

    /**
     * 회원메모 삭제
     * @param int $memo_id 메모번호
     * @return void
     */
    public function deleteMemo(int $memo_id): void
    {
        $query = "DELETE FROM {$this->memo_table} WHERE id = :id";
        Db::getInstance()->run($query, ['id' => $memo_id]);
    }


    /**
     * 목록 검색 조건 추가
     * @param array $params 검색 조건
     * @param array $wheres WHERE 절
     * @param array $values 바인딩 값
     * @return void
     */
    private function addSearchConditions(?array &$wheres = [], ?array &$values = [], ?array $params = [])
    {
        if (isset($params['status'])) {
            if ($params['status'] == 'leave') {
                $wheres[] = 'member.mb_leave_date is not null';
            } elseif ($params['status'] == 'intercept') {
                $wheres[] = 'member.mb_intercept_date is not null';
            } elseif ($params['status'] == 'normal') {
                $wheres[] = 'member.mb_leave_date is null';
                $wheres[] = 'member.mb_intercept_date is null';
            }
        }

        if (isset($params['keyword'])) {
            $wheres[] = "(member.mb_id LIKE :keyword1 OR member.mb_name LIKE :keyword2 OR member.mb_nick LIKE :keyword3)";
            $values["keyword1"] = "%{$params['keyword']}%";
            $values["keyword2"] = "%{$params['keyword']}%";
            $values["keyword3"] = "%{$params['keyword']}%";
        }
    }
}
