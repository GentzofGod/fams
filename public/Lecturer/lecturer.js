
// Tab Switching Logic
document.addEventListener("DOMContentLoaded",function () {
  fetchCourses();
  fetchEnrolledCourses();
  loadLecturerProfile();

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
        case 'add-students':
          fetchCourses();
          fetchEnrolledCourses();
          loadLecturerProfile();
            break;
        case 'manage-students':
          fetchEnrolledCourses() 
            break;
        case 'start-attendance': 
            break;  // <-- ADD THIS BREAK
        case 'view-attendance':
            break;
        case 'attendance-summary':
            break;
    }
    
    });
  });

  
function fetchCourses() {
  fetch('../../api/lecturer/get_courses.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const courseSelectAdd = document.getElementById('courseSelectAdd');
        const courseSelectBulk = document.getElementById('courseSelectBulk');
        
        courseSelectAdd.innerHTML = '';
        courseSelectBulk.innerHTML = '';

        data.courses.forEach(course => {
          console.log(course);  // Log each course to see if the ID is correct

          const option1 = document.createElement('option');
          const courseId = course._id?.$oid || course._id;
          console.log('Course ID:', courseId); // Debugging the course ID

          option1.value = courseId;  // Ensure the value is set correctly
          option1.textContent = `${course.course_code} - ${course.course_title}`;
          courseSelectAdd.appendChild(option1);

          const option2 = document.createElement('option');
          option2.value = courseId;  // Ensure the value is set correctly
          option2.textContent = `${course.course_code} - ${course.course_title}`;
          courseSelectBulk.appendChild(option2);
        });

      } else {
        alert(data.message || 'Failed to load courses');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error fetching courses');
    });
}
  
  // ADD SINGLE STUDENT
document.getElementById('addStudentForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const courseId = document.getElementById('courseSelectAdd').value;
  console.log(courseId);
  const matricNo = document.getElementById('matricNumber').value.trim();


  if (!courseId || !matricNo) return alert('All fields are required.');

  fetch('../../api/lecturer/add_student_to_course.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ course_id: courseId, matric_no: matricNo })
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) document.getElementById('addStudentForm').reset();
    })
    .catch(err => console.error('Error:', err));
});

// ENABLE BULK UPLOAD BUTTON
document.getElementById('bulkUploadFile').addEventListener('change', function () {
  document.getElementById('uploadStudentsBtn').disabled = !this.files.length;
});

// BULK UPLOAD
document.getElementById('uploadStudentsBtn').addEventListener('click', function (e) {
  e.preventDefault();

  const courseId = document.getElementById('courseSelectBulk').value;
  const fileInput = document.getElementById('bulkUploadFile');

  if (!courseId || !fileInput.files.length) return alert('Please select course and upload file.');

  const formData = new FormData();
  formData.append('course_id', courseId);
  formData.append('file', fileInput.files[0]);

  fetch('../../api/lecturer/bulk_upload_students.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      fileInput.value = '';
      document.getElementById('uploadStudentsBtn').disabled = true;
    })
    .catch(err => console.error('Upload failed:', err));
});


    function loadLecturerProfile() {
      fetch('../../api/lecturer/get_lecturer_profile.php')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('lecturerName').textContent = data.name;
            document.getElementById('lecturerEmail').textContent = data.email;
            document.getElementById('lecturerStaffNo').textContent = data.staff_number;
    
            const courseList = document.getElementById('assignedCoursesList');
            courseList.innerHTML = '';
    
            if (data.courses.length === 0) {
              courseList.innerHTML = '<li>No courses assigned yet.</li>';
            } else {
              data.courses.forEach(course => {
                const li = document.createElement('li');
                li.textContent = `${course.course_code} - ${course.course_title} - ${course.credit_load} Units`;
                courseList.appendChild(li);
              });
            }
          } else {
            window.location.href = "../login.html";
          }
        })
        .catch(err => {
          console.error(err);
          alert('Error fetching profile');
        });
    }
    
    // Call this on page load
    document.addEventListener('DOMContentLoaded', loadLecturerProfile);
    
       // Logout functionality
        document.getElementById("logoutBtn").addEventListener("click", () => {
          fetch("../../api/auth/logout.php")
            .then(() => window.location.href = "../login.html")
            .catch(err => console.error("Logout failed:", err));
        });
        
        // Toggle Profile Details
  document.getElementById('toggleProfileBtn').addEventListener('click', function() {
      const lecturerProfile = document.getElementById('lecturerProfile');
      
      // Toggle visibility of the entire lecturer profile div
      if (lecturerProfile.style.display === 'none') {
        lecturerProfile.style.display = 'block';
        this.textContent = 'Hide Profile Details'; // Change button text
      } else {
        lecturerProfile.style.display = 'none';
        this.textContent = 'Show Profile Details'; // Change button text
      }
    });



// Fetch courses from the server
function fetchEnrolledCourses() {
  fetch('../../api/lecturer/get_courses.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const courseSelectManage = document.getElementById('courseSelectManage');
        courseSelectManage.innerHTML = '';

        data.courses.forEach((course, index) => {
          const option = document.createElement('option');
          option.value = course._id?.$oid || course._id;
          option.textContent = `${course.course_code} - ${course.course_title}`;
          courseSelectManage.appendChild(option);
        });

        // Automatically fetch students for the first course
        if (data.courses.length > 0) {
          const firstCourseId = data.courses[0]._id?.$oid || data.courses[0]._id;
          courseSelectManage.value = firstCourseId;
          fetchEnrolledStudents(firstCourseId);
        }

        // Set up listener for course change
        courseSelectManage.addEventListener('change', function () {
          const selectedCourseId = this.value;
          if (selectedCourseId) {
            fetchEnrolledStudents(selectedCourseId);
          }
        });
      } else {
        alert(data.message || 'Failed to load courses');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error fetching courses');
    });
}



// Fetch students enrolled in the selected course
function fetchEnrolledStudents(courseId) {
  fetch(`../../api/lecturer/get_enrolled_students.php?course_id=${courseId}`)
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              displayStudentTable(data.students);
          } else {
              alert(data.message || 'Failed to fetch students');
          }
      })
      .catch(err => {
          console.error(err);
          alert('Error fetching students');
      });
}

// Display the list of enrolled students in a table
function displayStudentTable(students) {
  const studentTableContainer = document.getElementById('studentTableContainer');
  studentTableContainer.innerHTML = '';  // Clear existing table content

  if (students.length === 0) {
      studentTableContainer.innerHTML = '<p>No students enrolled in this course.</p>';
      return;
  }

  let tableHTML = `<table border="1">
                      <thead>
                          <tr>
                              <th>Matric Number</th>
                              <th>Full Name</th>
                              <th>Department</th>
                              <th>Level</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>`;

  students.forEach(student => {
      tableHTML += `<tr>
                      <td>${student.matric_no}</td>
                      <td>${student.fullname}</td>
                      <td>${student.department}</td>
                      <td>${student.level}</td>
                      <td><button onclick="removeStudent('${student.matric_no}')">Remove</button></td>
                  </tr>`;
  });

  tableHTML += `</tbody></table>`;

  studentTableContainer.innerHTML = tableHTML;
}

// Function to remove a student (optional feature)
function removeStudent(matricNo) {
  if (confirm(`Are you sure you want to remove student ${matricNo}?`)) {
      // Call the API to remove the student from the course
      fetch(`../../api/lecturer/remove_student_from_course.php`, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json'
          },
          body: JSON.stringify({
              matric_no: matricNo
          })
      })
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              alert('Student removed successfully');
              const courseId = document.getElementById('courseSelectManage').value;
              displayStudentTable(students); // Refresh the student list
          } else {
              alert('Failed to remove student');
          }
      })
      .catch(err => console.error('Error:', err));
  }
}

  });
  












  