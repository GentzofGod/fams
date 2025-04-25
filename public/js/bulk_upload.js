document.getElementById('bulkUploadForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const fileInput = document.getElementById('fileInput').files[0];

    if (!fileInput) {
        alert("Please select a file!");
        return;
    }

    let formData = new FormData();
    formData.append("file", fileInput);

    fetch("../../api/auth/bulk_upload.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("uploadMessage").innerText = data.message;
    })
    .catch(error => console.error("Error:", error));
});





document.getElementById('bulkStudentUploadForm').addEventListener('submit', function (e) {
    e.preventDefault();
    let studentFile = document.getElementById('studentFile').files[0];

    if (!studentFile) {
        alert("Please select a file!");
        return;
    }

    let formData = new FormData();
    formData.append("studentFile", studentFile); // check back

    fetch("../../api/auth/bulk_upload_students.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("uploadStatus").innerText = data.message;
    })
    .catch(error => console.error("Error:", error));
});
