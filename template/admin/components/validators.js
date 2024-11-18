// 이메일 유효성 검사
export const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

// 사업자등록번호 유효성 검사 (12자리, 숫자와 하이픈만 허용)
export const isValidBusinessNumber = (value) => {
  const numberOnly = value.replace(/-/g, '');
  const validCharacters = /^[\d-]+$/.test(value);
  const validLength = numberOnly.length === 12;
return validCharacters && validLength;
};

// 부가통신사업자번호 유효성 검사 (5자리 숫자만 허용)
export const isValidZipCode = (value) => {
  return /^\d{5}$/.test(value);
};

// 접근가능,차단 IP 유효성 검사 (숫자, `.`, `*` 만 입력가능`*` = 0~255)
export const isValidIpAddress = (value) => {
  // 빈 값 체크
  if (!value) return false;
  
  // 숫자, 점, * 외의 문자가 있는지 체크
  if (!/^[\d.*]+$/.test(value)) {
    return false;
  }
  
  // 점으로 분리
  const parts = value.split('.');
  
  // IP 주소는 4개의 파트로 구성되어야 함
  if (parts.length !== 4) {
    return false;
  }
  
  // 각 파트 검증
  return parts.every(part => {
    // * 인 경우 허용
    if (part === '*') {
      return true;
    }
    
    // 숫자로 변환
    const num = parseInt(part, 10);
    
    // 숫자가 아니거나 범위를 벗어나는 경우
    if (isNaN(num) || num < 0 || num > 255) {
      return false;
    }
    
    // 원래 문자열과 숫자를 문자열로 변환한 것이 다른 경우
    // (예: '01' !== '1' -> leading zero 체크)
    if (part !== num.toString()) {
      return false;
    }
    
    return true;
  });
};