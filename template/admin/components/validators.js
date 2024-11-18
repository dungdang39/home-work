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