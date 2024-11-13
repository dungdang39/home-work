// validator.js

/**
 * 이메일 형식 검사
 * @param {*} email 
 * @returns boolean
 */
function validateEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(String(email).toLowerCase());
}

/**
 * 이미지 파일 검사
 * @param {*} file
 * @returns boolean
 */
function isValidImageFile(file) {
    return file.type.startsWith('image/');
}

/**
 * 파일 확장자 검사
 * @param {*} file 
 * @param {*} allowedExtensions 
 * @returns boolean
 */
function isValidFileExtension(file, allowedExtensions) {
    const fileExtension = file.name.split('.').pop().toLowerCase();
    return allowedExtensions.includes(fileExtension);
}