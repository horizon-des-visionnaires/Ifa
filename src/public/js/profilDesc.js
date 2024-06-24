document.addEventListener("DOMContentLoaded", function() {
    const textarea = document.getElementById("inputFieldDesc");
    const charCount = document.getElementById("charCount");
    const maxBytes = 255;

    function getByteLength(str) {
        return new TextEncoder().encode(str).length;
    }

    textarea.addEventListener("input", function() {
        let value = textarea.value;
        let byteLength = getByteLength(value);

        while (byteLength > maxBytes) {
            value = value.substring(0, value.length - 1);
            byteLength = getByteLength(value);
        }

        textarea.value = value;
        charCount.textContent = `${byteLength}/${maxBytes}`;
    });
});