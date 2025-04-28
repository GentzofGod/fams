document.addEventListener("DOMContentLoaded", () => {


    loadCourses(); // Automatically fetch all courses on initial load

    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");
  
    tabButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        tabButtons.forEach(b => b.classList.remove("active"));
        tabContents.forEach(c => c.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).classList.add("active");
  
        // Load respective data
        switch (btn.dataset.tab) {
          case 'view-courses':
              loadCourses();
              break;
          case 'assign-course':
              loadCourseDropdown();
              loadLecturers();
              break;
          case 'assignedCoursesTab': 
              loadAssignedCourses();  
              break;  // <-- ADD THIS BREAK
          case 'view-hod-students':
              loadHodStudents();
              break;
          case 'view-attendance':
              loadAttendanceReports();
              break;
      }
      
      });
    });
  





    // ENABLE BULK UPLOAD BUTTON
document.getElementById('bulkUploadCourseFile').addEventListener('change', function () {
  document.getElementById('uploadCourseBtn').disabled = !this.files.length;
});

// BULK UPLOAD
document.getElementById('uploadCourseBtn').addEventListener('click', function (e) {
  e.preventDefault();

  const fileInput = document.getElementById('bulkUploadCourseFile');

  if (!fileInput.files.length) return alert('Please select file.');

  const formData = new FormData();
  formData.append('file', fileInput.files[0]);

  fetch('../../api/hod/bulk_upload_courses.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      fileInput.value = '';
      document.getElementById('uploadCourseBtn').disabled = true;
    })
    .catch(err => console.error('Upload failed:', err));
});







    // Add Course
    document.getElementById("addCourseForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      const form = new FormData(e.target);
      const res = await fetch('../../api/hod/add_course.php', { method: 'POST', body: form });
      const data = await res.json();
      alert(data.message);
      if (data.success) {
        e.target.reset();
        loadCourses();
      }
    });


  //load courses
    async function loadCourses() {
        try {
          const res = await fetch("../../api/hod/get_courses.php");
          const data = await res.json();
          const container = document.getElementById("coursesTable");
      
          if (!data.success || !Array.isArray(data.courses)) {
            container.innerHTML = "<p>Failed to load courses.</p>";
            return;
          }
      
          if (data.courses.length === 0) {
            container.innerHTML = "<p>No courses found.</p>";
            return;
          }
      
                  // Sort courses alphabetically by course code
        data.courses.sort((a, b) => a.course_code.localeCompare(b.course_code));

          container.innerHTML = `
            <table>
              <thead>
                <tr>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th>Credit Load</th>
                  <th>Created At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                ${data.courses.map(course => `
                  <tr>
                    <td>${course.course_code}</td>
                    <td>${course.course_title}</td>
                    <td>${course.credit_load}</td>
                    <td>${course.created_at}</td>
                    <td>
<button class="edit-course-btn"
        data-id="${course.id}"
        data-code="${course.course_code}"
        data-title="${course.course_title}"
        data-title="${course.credit_load}"
        >Edit</button>
<button class="delete-course-btn" data-id="${course.id}">Delete</button>

                    </td>
                  </tr>
                `).join("")}
              </tbody>
            </table>
          `;
      
          // Attach action handlers
          document.querySelectorAll('.edit-course-btn').forEach(btn => {
            btn.addEventListener('click', () => openEditCourseModal(btn.dataset));
          });
          
          document.querySelectorAll('.delete-course-btn').forEach(btn => {
            btn.addEventListener('click', () => confirmDeleteCourse(btn.dataset.id));
          });
          
      
        } catch (err) {
          console.error("Error loading courses:", err);
          document.getElementById("coursesTable").innerHTML = "<p>Error loading courses.</p>";
        }
      }
      
  //showmodal
  function showModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      modal.style.display = 'flex'; // Only works if your modal uses flex display
    }
  }
  
  function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      modal.style.display = 'none';
    }
  }
  
  // Allow closing with X
  document.querySelectorAll(".modal .close").forEach(closeBtn => {
    closeBtn.addEventListener("click", () => {
      closeModal(closeBtn.closest(".modal").id);
    });
  });
  


// Open Edit Course Modal
function openEditCourseModal(course) {
    document.getElementById('editCourseId').value = course.id;
    document.getElementById('editCourseCode').value = course.code;
    document.getElementById('editCourseTitle').value = course.title;
    showModal('editCourseModal');
  }
  
  // Submit Edited Course
  document.getElementById('editCourseForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = new FormData(this);
    const res = await fetch('../../api/hod/edit_course.php', {
      method: 'POST',
      body: form
    });
    const data = await res.json();
    alert(data.message);
    if (data.success) {
      closeModal('editCourseModal');
      loadCourses();
    }
  });
  
  // Confirm Delete
  function confirmDeleteCourse(courseId) {
    const confirmModal = document.getElementById('deleteConfirmModal');
    confirmModal.style.display = 'block';
  
    document.getElementById('confirmDeleteBtn').onclick = () => {
      deleteCourse(courseId);
      confirmModal.style.display = 'none';
    };
  
    document.getElementById('cancelDeleteBtn').onclick = () => {
      confirmModal.style.display = 'none';
    };
  }
  
  // Delete Course
  async function deleteCourse(courseId) {
    const form = new FormData();
    form.append('id', courseId);
  
    const res = await fetch('../../api/hod/delete_course.php', {
      method: 'POST',
      body: form
    });
  
    const data = await res.json();
    alert(data.message);
    if (data.success) {
      loadCourses();
    }
  }
  
    // Load Dropdowns for Assignment

    async function loadCourseDropdown() {
        const res = await fetch('../../api/hod/get_courses.php');
        const data = await res.json();
        const select = document.getElementById('courseSelect');
      
        if (data.success) {
          select.innerHTML = data.courses.map(course => `
            <option value="${course.id}">${course.course_code} - ${course.course_title}</option>
          `).join('');
        } else {
          select.innerHTML = '<option disabled>No courses found</option>';
        }
      }
      
      async function loadLecturers() {
        const res = await fetch('../../api/hod/get_Lecturers.php');
        const data = await res.json();
        const select = document.getElementById('LecturerSelect');
      
        if (data.success) {
          select.innerHTML = data.Lecturers.map(lec => `
            <option value="${lec.id}">${lec.name}</option>
          `).join('');
        } else {
          select.innerHTML = '<option disabled>No Lecturers found</option>';
        }
      }
      

      //form submit assign
      document.getElementById("assignCourseForm").addEventListener("submit", async function (e) {
  e.preventDefault();
  const form = new FormData(this);
  const res = await fetch('../../api/hod/assign_course.php', {
    method: 'POST',
    body: form
  });
  const data = await res.json();
  alert(data.message);
  if (data.success) {
    this.reset();
  }
});


    // Load Assigned Courses
    function loadAssignedCourses() {
        fetch("../../api/hod/get_assigned_courses.php")
            .then(res => res.json())
            .then(data => {
                const tableBody = document.querySelector("#assignedCoursesTable tbody");
                tableBody.innerHTML = "";
    
                data.assigned_courses.forEach(item => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${item.course_code}  -  ${item.course_title}  -  ${item.credit_load}</td>
                        <td>${item.Lecturer_name}  -  ${item.Lecturer_email}  -  ${item.phone_number}</td>
                        <td>
                            <button class="editAssignedBtn" data-id="${item.id}">Edit</button>
                            <button class="deleteAssignedBtn" data-id="${item.id}">Delete</button>
                        </td>
                    `;
    
                    tableBody.appendChild(row);
                });
    
                // Add edit and delete listeners
                document.querySelectorAll(".editAssignedBtn").forEach(btn => {
                    btn.addEventListener("click", () => {
                        const id = btn.getAttribute("data-id");
                        openEditAssignedModal(id);
                    });
                });
    
                document.querySelectorAll(".deleteAssignedBtn").forEach(btn => {
                    btn.addEventListener("click", () => {
                        const id = btn.getAttribute("data-id");
                        confirmDeleteAssignedCourse(id);
                    });
                });
            });
    }
    






    let allLecturers = []; // Cache this after first fetch

    function fetchLecturersForEditModal() {
        return fetch("../../api/hod/get_Lecturers.php")
            .then(res => res.json())
            .then(data => {
                allLecturers = data.Lecturers || [];
    
                const select = document.getElementById("editLecturerSelect");
                select.innerHTML = "";
                allLecturers.forEach(lec => {
                    const option = document.createElement("option");
                    option.value = lec.id;
                    option.textContent = `${lec.name} (${lec.email})`;
                    select.appendChild(option);
                });
            });
    }
    
    function openEditAssignedModal(id) {
        document.getElementById("editAssignedId").value = id;
        fetchLecturersForEditModal().then(() => {
            document.getElementById("editAssignedModal").style.display = "block";
        });
    }
    
    function closeEditAssignedModal() {
        document.getElementById("editAssignedModal").style.display = "none";
    }
    
    // Submit updated assignment
    document.getElementById("editAssignedForm").addEventListener("submit", function (e) {
        e.preventDefault();
    
        const id = document.getElementById("editAssignedId").value;
        const Lecturer_id = document.getElementById("editLecturerSelect").value;
    
        fetch("../../api/hod/edit_assigned_course.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id, Lecturer_id })
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert("Assignment updated successfully.");
                closeEditAssignedModal();
                loadAssignedCourses();
            } else {
                alert("Failed to update.");
            }
        });
    });
    

//confirm assign course delete
    function confirmDeleteAssignedCourse(id) {
        const confirmed = confirm("Are you sure you want to delete this assigned course?");
        if (confirmed) {
            deleteAssignedCourse(id);
        }
    }
    function deleteAssignedCourse(id) {
        console.log("Deleting assigned course with ID:", id); // Debug line
    
        fetch("../../api/hod/delete_assigned_course.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            console.log("Delete response:", data); // Debug line
            if (data.success) {
                alert("Assigned course deleted successfully!");
                loadAssignedCourses(); // Refresh table
            } else {
                alert("Failed to delete assigned course.");
            }
        })
        .catch(err => {
            console.error("Error deleting course:", err);
            alert("An error occurred while deleting the course.");
        });
    }
        

    let currentPage = 1;
    const limit = 20;
    async function loadHodStudents(page = 1) {
      currentPage = page;
  
      // Get HOD department dynamically
      const department = document.getElementById("hodDept").value;
      const levelFilter = document.getElementById("filterLevel").value;
  
      const res = await fetch(`../../api/hod/get_students.php?department=${encodeURIComponent(department)}`);
      const data = await res.json();
  
      const container = document.getElementById("studentsTable");
      const paginationContainer = document.getElementById("paginationControls");
  
      if (data.success) {
          let students = data.students;
  
          const filteredStudents = levelFilter
              ? students.filter(s => s.level === levelFilter)
              : students;
  
                      // Pagination calculations after filtering
        let totalFilteredStudents = filteredStudents.length;
        let totalPages = Math.ceil(totalFilteredStudents / limit);
        let paginatedStudents = filteredStudents.slice((currentPage - 1) * limit, currentPage * limit);

        if (paginatedStudents.length > 0) {
              container.innerHTML = `
                 <table>
                  <thead>
                      <tr>
                          <th>Matric No</th>
                          <th>Full Name</th>
                          <th>Department</th>
                          <th>Programme</th>
                          <th>Level</th>
                          <th>Gender</th>
                          <th>Phone Number</th>
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
                                  <button class="edit-student-btn" data-id="${student._id}" 
                                      data-matric="${student.matric_no}" 
                                      data-name="${student.fullname}" 
                                      data-department="${student.department}" 
                                      data-programme="${student.programme}" 
                                      data-level="${student.level}"
                                      data-gender="${student.gender}" 
                                      data-phone="${student.phone_number}"
                                      data-email="${student.email}">Edit</button>
                                  <button class="delete-student-btn" data-id="${student._id}">Delete</button>
                              </td>
                              <td>${student.created_at}</td>
                          </tr>`).join("")}
                  </tbody>
              </table>`;
            // Update pagination
            paginationContainer.innerHTML = "";
            let prevButton = document.createElement("button");
            prevButton.textContent = "Previous";
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener("click", () => loadHodStudents(currentPage - 1));
            paginationContainer.appendChild(prevButton);

            let pageInfo = document.createElement("span");
            pageInfo.textContent = ` Page ${currentPage} of ${totalPages} `;
            paginationContainer.appendChild(pageInfo);

            let nextButton = document.createElement("button");
            nextButton.textContent = "Next";
            nextButton.disabled = currentPage === totalPages || totalPages === 0;
            nextButton.addEventListener("click", () => loadHodStudents(currentPage + 1));
            paginationContainer.appendChild(nextButton);
              // Events
              document.querySelectorAll(".student-row").forEach(row => {
                  row.addEventListener("click", () => showStudentDetails(row.dataset));
              });
  
              document.querySelectorAll(".edit-student-btn").forEach(btn => {
                  btn.addEventListener("click", e => {
                      e.stopPropagation();
                      openEditStudentModal(btn.dataset);
                  });
              });
  
              document.querySelectorAll(".delete-student-btn").forEach(btn => {
                  btn.addEventListener("click", e => {
                      e.stopPropagation();
                      deleteStudent(btn.dataset.id);
                  });
              });
  
          } else {
              container.innerHTML = `<p>No students found for the selected level.</p>`;
              paginationContainer.innerHTML = "";
          }
      } else {
          container.innerHTML = `<p>Failed to load students.</p>`;
          paginationContainer.innerHTML = "";
      }
  }
  

    function showStudentDetails(data) {
      document.getElementById("detailMatric").innerText = data.matric;
      document.getElementById("detailName").innerText = data.name;
      document.getElementById("detailDept").innerText = data.department;
      document.getElementById("detailProgramme").innerText = data.programme;
      document.getElementById("detailLevel").innerText = data.level;
  
      document.getElementById("detailGender").innerText = data.gender;
      document.getElementById("detailEmail").innerText = data.email;
      document.getElementById("detailPhoneNo").innerText = data.phone;
  
      document.getElementById("detailCreated").innerText = data.created;
      showModal("studentDetailModal");
    }
    
    function applyFilters() {
        loadHodStudents(1);
    }
    
    document.getElementById("filterLevel").addEventListener("change", applyFilters);
    
    document.addEventListener("DOMContentLoaded", () => loadHodStudents());
    
  
    function openEditStudentModal(student) {
      document.getElementById("editStudentId").value = student.id;
      document.getElementById("editStudentMatric").value = student.matric;
      document.getElementById("editStudentName").value = student.name;
      document.getElementById("editStudentDept").value = student.department;
      document.getElementById("editStudentProgramme").value = student.programme;
      document.getElementById("editStudentLevel").value = student.level;
  
      document.getElementById("editStudentGender").value = student.gender;
      document.getElementById("editStudentEmail").value = student.email;
      document.getElementById("editStudentPhoneNo").value = student.phone;
  
      showModal("editStudentModal");
    }
  

  
    document.getElementById("editStudentForm").addEventListener("submit", async function (e) {
      e.preventDefault();
      const form = new FormData(this);
      const res = await fetch('../../api/superadmin/edit_student.php', { method: 'POST', body: form });
      const data = await res.json();
      alert(data.message);
      if (data.success) {
        closeModal("editStudentModal");
        loadHodStudents();
      }
    });
  
    async function deleteStudent(id) {
      if (confirm("Are you sure?")) {
        const form = new FormData();
        form.append("id", id);
        const res = await fetch('../../api/superadmin/remove_student.php', { method: 'POST', body: form });
        const data = await res.json();
        alert(data.message);
        if (data.success) loadHodStudents();
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
      else if (deleteTargetType === "student") loadHodStudents();
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
  



    // Attendance Reports by Course
    async function loadAttendanceReports() {
      const res = await fetch('../../api/hod/get_attendance_reports.php');
      const data = await res.json();
      const container = document.getElementById("attendanceReportsList");
      if (data.success) {
        container.innerHTML = data.reports.map(r => `
          <tr>
            <td>${r.course}</td>
            <td>${r.Lecturer}</td>
            <td>${r.total_students}</td>
            <td>${r.attended}</td>
            <td>${r.percentage}%</td>
          </tr>
        `).join('');
      }
    }
  
    function closeModal(id) {
      document.getElementById(id).style.display = "none";
    }

    window.addEventListener("DOMContentLoaded", async () => {
      try {
        const res = await fetch("../../api/hod/get_hod_info.php");
        const data = await res.json();
    
        if (data.success) {
          document.getElementById("welcomeText").innerText = `Welcome to the ${data.role} Dashboard! ${data.name}`;
          document.getElementById("hodEmail").innerText = data.email;
          document.getElementById("hodPhone").innerText = data.phone_number;
          document.getElementById("hodStaff").innerText = data.staff_number;
          document.getElementById("hodRole").innerText = data.role;
          document.getElementById("hodDept").innerText = data.department;
        } else {
          alert("You are not authorized. Please log in again.");
          window.location.href = "../login.html";
        }
      } catch (err) {
        console.error("Error fetching HOD info:", err);
      }
    });
    
    // Toggle logic
    document.getElementById("toggleDetailsBtn").addEventListener("click", () => {
      const details = document.getElementById("hodDetails");
      const toggleText = document.getElementById("toggleDetailsBtn");
    
      const isHidden = details.style.display === "none";
      details.style.display = isHidden ? "block" : "none";
    
      toggleText.innerText = isHidden ?  "▲" : "▼";
    });
    
    // Logout functionality
    document.getElementById("logoutBtn").addEventListener("click", () => {
      fetch("../../api/auth/logout.php")
        .then(() => window.location.href = "../login.html")
        .catch(err => console.error("Logout failed:", err));
    });
    
  