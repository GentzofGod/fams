document.addEventListener("DOMContentLoaded", () => {
  let submittingAdmin = false;
  let submittingStudent = false;
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach(button => {
    button.addEventListener("click", () => {
      tabButtons.forEach(btn => btn.classList.remove("active"));
      tabContents.forEach(tab => tab.classList.remove("active"));

      button.classList.add("active");
      const activeTab = document.getElementById(button.dataset.tab);
      activeTab.classList.add("active");

      if (button.dataset.tab === "view-admins") loadAdmins();
      if (button.dataset.tab === "view-students") loadStudents(1);
    });
  });

  // ADD ADMIN
  document.getElementById("addAdminForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    if (submittingAdmin) return;
    submittingAdmin = true;
try{
    const form = new FormData(this);
    const res = await fetch('../../api/superadmin/add_admin.php', { method: 'POST', body: form });
    const data = await res.json();
    alert(data.message);
    if (data.success) {
      this.reset();
      loadAdmins();
    }
  }catch(error){
alert("error adding admin" + error.message);
console.error(error);
  }finally{
    submittingAdmin = false;
  }});

  // ADD STUDENT
  document.getElementById("addStudentForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    if (submittingStudent) return;
    submittingStudent = true;

    const form = new FormData(this);
    const res = await fetch('../../api/superadmin/add_student.php', { method: 'POST', body: form });
    const data = await res.json();
    
    alert(data.message);
    
    if (data.success) {
        loadStudents();
        this.reset(); // Reset form after successful submission
        document.getElementById("department").value = ""; // Reset department dropdown
        document.getElementById("level").value = ""; // Reset level dropdown
    }

    submittingStudent = false;
});

  // LOAD ADMINS
// LOAD ADMINS with Filtering
async function loadAdmins() {
  try {
      console.log("Fetching admins...");
      const res = await fetch('../../api/superadmin/get_admins.php');
      const data = await res.json();
      const container = document.getElementById("adminsTable");

      if (data.success) {
          let admins = data.admins;

          // Get filter values
          const filterRole = document.getElementById("filterRole").value;
          const filterDepartment = document.getElementById("filterDepartment").value;

          // Apply Role Filter
          if (filterRole) {
              admins = admins.filter(admin => admin.role === filterRole);
          }

          // Apply Department Filter
          if (filterDepartment) {
              admins = admins.filter(admin => admin.department === filterDepartment);
          }

          admins.sort((a, b) => a.name.localeCompare(b.name));
          
          // Generate Admins Table
          container.innerHTML = `
          <table>
              <thead>
                  <tr>
                      <th>Name</th>
                      <th>Staff Number</th>
                      <th>Phone Number</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th>Department</th>
                      <th>Actions</th>
                      <th>Created At</th>
                  </tr>
              </thead>
              <tbody>
                  ${admins.length > 0 ? admins.map(admin => `
                      <tr 
                          data-id="${admin._id}" 
                          data-name="${admin.name}" 
                          data-designation="${admin.designation}" 
                          data-email="${admin.email}" 
                          data-role="${admin.role}" 
                          data-department="${admin.department}"
                          data-phone_number="${admin.phone_number}" 
                          data-staff_number="${admin.staff_number}"
                      >
                          <td>${admin.name}</td>
                          <td>${admin.staff_number}</td>
                          <td>${admin.phone_number}</td>
                          <td>${admin.email}</td>
                          <td>${admin.role}</td>
                          <td>${admin.department}</td>
                          <td>
                              <button class="edit-admin-btn">Edit</button>
                              <button class="delete-admin-btn">Delete</button>
                          </td>
                          <td>${admin.created_at}</td>
                      </tr>
                  `).join("") : `<tr><td colspan="8">No admins found.</td></tr>`}
              </tbody>
          </table>`;

      } else {
          container.innerHTML = `<p>Failed to load admins.</p>`;
      }
  } catch (error) {
      console.error("Error loading admins:", error);
      document.getElementById("adminsTable").innerHTML = `<p>Error loading admins.</p>`;
  }
}

//  Event Delegation for Edit and Delete Buttons
document.getElementById("adminsTable").addEventListener("click", function (e) {
  if (e.target.classList.contains("edit-admin-btn")) {
      const row = e.target.closest("tr");
      openEditAdminModal({
          id: row.dataset.id,
          name: row.dataset.name,
          email: row.dataset.email,
          staff_number: row.dataset.staff_number,
          designation: row.dataset.designation,
          phone_number: row.dataset.phone_number,
          role: row.dataset.role,
          department: row.dataset.department
      });
  } else if (e.target.classList.contains("delete-admin-btn")) {
      const row = e.target.closest("tr");
      deleteAdmin(row.dataset.id);
  }
});

//  Function to Open Edit Modal
function openEditAdminModal(adminData) {
  // Get modal elements safely
  const modal = document.querySelector("#editAdminModal");
  if (!modal) {
      console.error("❌ Error: Edit Admin Modal not found!");
      return;
  }

  // Find form fields inside modal
  const idField = modal.querySelector("input[name='admin_id']");
  const nameField = modal.querySelector("input[name='name']");
  const emailField = modal.querySelector("input[name='email']");

  const staffNoField = modal.querySelector("input[name='staff_number']");
  const designationField = modal.querySelector("select[name='designation']");
  const phoneNoField = modal.querySelector("input[name='phone_number']");

  const roleField = modal.querySelector("select[name='role']");
  const deptField = modal.querySelector("select[name='department']");

  // Ensure all fields exist before assigning values
  if (!idField || !nameField || !emailField || !staffNoField || !designationField|| !phoneNoField || !roleField || !deptField) {
      console.error("❌ Error: One or more input fields missing inside the modal!");
      return;
  }

  // Set values
  idField.value = adminData.id;
  nameField.value = adminData.name;
  emailField.value = adminData.email;

  staffNoField.value = adminData.staff_number;
  designationField.value = adminData.designation;
  phoneNoField.value = adminData.phone_number;

  roleField.value = adminData.role;
  deptField.value = adminData.department;

  // Show modal
  showModal("editAdminModal");
}


//  Handle Edit Admin Submission
document.getElementById("editAdminForm").addEventListener("submit", async function (e) {
  e.preventDefault();
  const form = new FormData(this);
  const res = await fetch('../../api/superadmin/edit_admin.php', { method: 'POST', body: form });
  const data = await res.json();
  alert(data.message);
  if (data.success) {
      closeModal("editAdminModal");
      loadAdmins();
  }
});

//  Handle Delete Admin
async function deleteAdmin(id) {
  console.log("Deleting admin with ID:", id); //  Debugging
  if (confirm("Are you sure?")) {
      const form = new FormData();
      form.append("id", id);
      const res = await fetch('../../api/superadmin/remove_admin.php', { method: 'POST', body: form });
      const data = await res.json();
      alert(data.message);
      if (data.success) loadAdmins();
  }
}

//  Attach Event Listeners to Filters
document.getElementById("filterRole").addEventListener("change", loadAdmins);
document.getElementById("filterDepartment").addEventListener("change", loadAdmins);

//  Load admins on page load
document.addEventListener("DOMContentLoaded", loadAdmins);

let currentPage = 1;
const limit = 20;

async function loadStudents(page = 1) {
    currentPage = page;

    const res = await fetch(`../../api/superadmin/get_students.php`);
    const data = await res.json();
    const container = document.getElementById("studentsTable");
    const paginationContainer = document.getElementById("paginationControls");

    // Get filter values
    let deptFilter = document.getElementById("filterDept").value;
    let levelFilter = document.getElementById("filterLevel").value;

    if (data.success) {
        let students = data.students;

        // Apply filters
        let filteredStudents = students.filter(student => {
            return (deptFilter === "" || student.department === deptFilter) &&
                   (levelFilter === "" || student.level === levelFilter);
        });

        // Sort by name
        filteredStudents.sort((a, b) => a.fullname.localeCompare(b.fullname));

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

            // Update pagination
            paginationContainer.innerHTML = "";
            let prevButton = document.createElement("button");
            prevButton.textContent = "Previous";
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener("click", () => loadStudents(currentPage - 1));
            paginationContainer.appendChild(prevButton);

            let pageInfo = document.createElement("span");
            pageInfo.textContent = ` Page ${currentPage} of ${totalPages} `;
            paginationContainer.appendChild(pageInfo);

            let nextButton = document.createElement("button");
            nextButton.textContent = "Next";
            nextButton.disabled = currentPage === totalPages || totalPages === 0;
            nextButton.addEventListener("click", () => loadStudents(currentPage + 1));
            paginationContainer.appendChild(nextButton);
        } else {
            container.innerHTML = `<p>No students found for the selected filters.</p>`;
            paginationContainer.innerHTML = "";
        }

        // Event Listeners for Actions
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
        container.innerHTML = `<p>Failed to load students.</p>`;
        paginationContainer.innerHTML = "";
    }
}

// Function to apply filters and reload students
function applyFilters() {
    loadStudents(1); // Always start from first page when applying filters
}

// Attach event listeners to filter dropdowns
document.getElementById("filterDept").addEventListener("change", applyFilters);
document.getElementById("filterLevel").addEventListener("change", applyFilters);

// Load students when the page loads
document.addEventListener("DOMContentLoaded", () => loadStudents());


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

window.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch("../../api/superadmin/get_superadmin_info.php");
    const data = await res.json();

    if (data.success) {
      // Set welcome message
      document.getElementById("welcomeText").innerText =
        `  Welcome to the ${data.role} Dashboard! ${data.name}`;

      // Set detail values
     // document.getElementById("adminName").innerText = data.name;
      document.getElementById("adminEmail").innerText = data.email;
      document.getElementById("adminPhone").innerText = data.phone_number;
      document.getElementById("adminStaff").innerText = data.staff_number;
      document.getElementById("adminRole").innerText = data.role;

      // Toggle logic
      const toggleBtn = document.getElementById("toggleDetailsBtn");
      const detailsDiv = document.getElementById("adminDetails");

      toggleBtn.addEventListener("click", () => {
        const isHidden = detailsDiv.style.display === "none";
        detailsDiv.style.display = isHidden ? "block" : "none";
        toggleBtn.innerText = isHidden ? "▲" : "▼";
      });

      // Logout button
      const logoutBtn = document.getElementById("logoutBtn");
      logoutBtn.addEventListener("click", async () => {
        await fetch("../../api/auth/logout.php");
        window.location.href = "../login.html";
      });

    } else {
      alert("You are not authorized. Please log in again.");
      window.location.href = "../login.html";
    }
  } catch (err) {
    console.error("Error fetching Super Admin info:", err);
  }
});
