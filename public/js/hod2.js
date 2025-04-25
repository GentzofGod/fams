// Load students by selected level
async function loadStudents() {
  
document.getElementById("studentLevelFilter").addEventListener("submit", async function(e) {
  e.preventDefault();

  const level = document.getElementById("levelFilter").value;
  if (!level) {
    alert("Please select a level.");
    return;
  }

  const form = new FormData();
  form.append("level", level);

  try {
    const res = await fetch("../../api/hod/get_students_by_level.php", {
      method: "POST",
      body: form
    });

    const data = await res.json();
    const list = document.getElementById("studentsList");
    list.innerHTML = "";

    if (data.success && data.students.length > 0) {
      const table = document.createElement("table");
      table.innerHTML = `
   <table>
                    <thead>
                        <tr>
                            <th>Matric No</th>
                            <th>Full Name</th>
                            <th>Department</th>
                            <th>Programme</th>
                            <th>Level</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${paginatedStudents.map(student => `
                            <tr class="student-row" 
                                data-id="${student._id}" 
                                data-matric="${student.matric_no}" 
                                data-name="${student.fullname}" 
                                data-department="${student.department}" 
                                data-programme="${student.programme}" 
                                data-level="${student.level}" 
                                data-gender="${student.gender}" 
                                data-phone="${student.phone_number}" 
                                data-email="${student.email}" 
                                data-created="${student.created_at}">
                                <td>${student.matric_no}</td>
                                <td>${student.fullname}</td>
                                <td>${student.department}</td>
                                <td>${student.programme}</td>
                                <td>${student.level}</td>
                                <td>${student.gender}</td>
                                <td>${student.phone_number}</td>
                                <td>${student.email}</td>
                                <td>
                                    <button class="edit-student-btn" 
                                        data-id="${student._id}" 
                                        data-matric="${student.matric_no}" 
                                        data-name="${student.fullname}" 
                                        data-department="${student.department}" 
                                        data-programme="${student.programme}" 
                                        data-level="${student.level}"
                                        data-gender="${student.gender}" 
                                        data-phone="${student.phone_number}"
                                        data-email="${student.email}"  
                                        >Edit</button>
                                    <button class="delete-student-btn" data-id="${student._id}">Delete</button>
                                </td>
                                <td>${student.created_at}</td>
                            </tr>`).join("")}
                    </tbody>
                </table>`;
      list.appendChild(table);

      // Attach handlers
      document.querySelectorAll(".editStudentBtn").forEach(btn => {
        btn.addEventListener("click", () => openEditStudentModal(btn.dataset));
      });

      document.querySelectorAll(".deleteStudentBtn").forEach(btn => {
        btn.addEventListener("click", () => deleteStudent(btn.dataset.id));
      });

    } else {
      list.innerHTML = "<p>No students found for selected level.</p>";
    }

  } catch (err) {
    console.error("Error loading students:", err);
    alert("Failed to fetch students.");
  }

});

}
// Load students when the page loads
document.addEventListener("DOMContentLoaded", () => loadStudents());


    function openEditStudentModal(student) {
      document.getElementById("editStudentId").value = student.id;
      document.getElementById("editStudentMatric").value = student.matric;
      document.getElementById("editStudentName").value = student.name;
      document.getElementById("editStudentDept").value = student.department;
      document.getElementById("editStudentLevel").value = student.level;
      showModal("editStudentModal");
    }

    
  function showStudentDetails(data) {
    document.getElementById("detailPassport").src = data.passport;
    document.getElementById("detailMatric").innerText = data.matric;
    document.getElementById("detailName").innerText = data.name;
    document.getElementById("detailDept").innerText = data.department;
    document.getElementById("detailLevel").innerText = data.level;
    document.getElementById("detailCreated").innerText = data.created;
    showModal("studentDetailModal");
  }
  
    document.getElementById("editStudentForm").addEventListener("submit", async function (e) {
      e.preventDefault();
      const form = new FormData(this);
      const res = await fetch('../../api/superadmin/edit_student.php', { method: 'POST', body: form });
      const data = await res.json();
      alert(data.message);
      if (data.success) {
        closeModal("editStudentModal");
        loadStudents();
      }
    });
  
    async function deleteStudent(id) {
      if (confirm("Are you sure?")) {
        const form = new FormData();
        form.append("id", id);
        const res = await fetch('../../api/superadmin/remove_student.php', { method: 'POST', body: form });
        const data = await res.json();
        alert(data.message);
        if (data.success) loadStudents();
      }
    }
  
    function showModal(id) {
      document.getElementById(id).style.display = "block";
    }
  
    function closeModal(id) {
      document.getElementById(id).style.display = "none";
    }
  
    document.querySelectorAll(".modal .close").forEach(closeBtn => {
      closeBtn.addEventListener("click", () => {
        closeModal(closeBtn.closest(".modal").id);
      });
    });  









  // confirmation button

let deleteTargetId = null;
let deleteTargetType = null;

function confirmDelete(type, id) {
  deleteTargetId = id;
  deleteTargetType = type;
  showModal("deleteConfirmModal");
}

document.getElementById("confirmDeleteBtn").addEventListener("click", async () => {
  if (!deleteTargetId || !deleteTargetType) return;

  const form = new FormData();
  form.append("id", deleteTargetId);

  const res = await fetch(`../../api/superadmin/remove_${deleteTargetType}.php`, {
    method: 'POST',
    body: form
  });

  const data = await res.json();
  alert(data.message);

  closeModal("deleteConfirmModal");

  if (data.success) {
    if (deleteTargetType === "admin") loadAdmins();
    else if (deleteTargetType === "student") loadStudents();
  }

  // Reset
  deleteTargetId = null;
  deleteTargetType = null;
});

document.getElementById("cancelDeleteBtn").addEventListener("click", () => {
  closeModal("deleteConfirmModal");
  deleteTargetId = null;
  deleteTargetType = null;
});
