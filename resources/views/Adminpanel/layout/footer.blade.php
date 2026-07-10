 <footer class="footer">
     <div class="container-fluid d-flex justify-content-between">
         <nav class="pull-left">
             <ul class="nav">
                 <li class="nav-item">
                     <a class="nav-link" href="http://www.themekita.com">
                         ThemeKita
                     </a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link" href="#"> Help </a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link" href="#"> Licenses </a>
                 </li>
             </ul>
         </nav>
         <div class="copyright">
             2024, made with <i class="fa fa-heart heart text-danger"></i> by
             <a href="http://www.themekita.com">ThemeKita</a>
         </div>
         <div>
             Distributed by
             <a target="_blank" href="https://themewagon.com/">ThemeWagon</a>.
         </div>
     </div>
 </footer>
 </div>

 <!-- Custom template | don't include it in your project! -->
 <div class="custom-template">
     <div class="title">Settings</div>
     <div class="custom-content">
         <div class="switcher">
             <div class="switch-block">
                 <h4>Logo Header</h4>
                 <div class="btnSwitch">
                     <button type="button" class="selected changeLogoHeaderColor" data-color="dark"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="blue"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="purple"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="light-blue"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="green"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="orange"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="red"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="white"></button>
                     <br />
                     <button type="button" class="changeLogoHeaderColor" data-color="dark2"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="blue2"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="purple2"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="light-blue2"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="green2"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="orange2"></button>
                     <button type="button" class="changeLogoHeaderColor" data-color="red2"></button>
                 </div>
             </div>
             <div class="switch-block">
                 <h4>Navbar Header</h4>
                 <div class="btnSwitch">
                     <button type="button" class="changeTopBarColor" data-color="dark"></button>
                     <button type="button" class="changeTopBarColor" data-color="blue"></button>
                     <button type="button" class="changeTopBarColor" data-color="purple"></button>
                     <button type="button" class="changeTopBarColor" data-color="light-blue"></button>
                     <button type="button" class="changeTopBarColor" data-color="green"></button>
                     <button type="button" class="changeTopBarColor" data-color="orange"></button>
                     <button type="button" class="changeTopBarColor" data-color="red"></button>
                     <button type="button" class="selected changeTopBarColor" data-color="white"></button>
                     <br />
                     <button type="button" class="changeTopBarColor" data-color="dark2"></button>
                     <button type="button" class="changeTopBarColor" data-color="blue2"></button>
                     <button type="button" class="changeTopBarColor" data-color="purple2"></button>
                     <button type="button" class="changeTopBarColor" data-color="light-blue2"></button>
                     <button type="button" class="changeTopBarColor" data-color="green2"></button>
                     <button type="button" class="changeTopBarColor" data-color="orange2"></button>
                     <button type="button" class="changeTopBarColor" data-color="red2"></button>
                 </div>
             </div>
             <div class="switch-block">
                 <h4>Sidebar</h4>
                 <div class="btnSwitch">
                     <button type="button" class="changeSideBarColor" data-color="white"></button>
                     <button type="button" class="selected changeSideBarColor" data-color="dark"></button>
                     <button type="button" class="changeSideBarColor" data-color="dark2"></button>
                 </div>
             </div>
         </div>
     </div>
     <div class="custom-toggle">
         <i class="icon-settings"></i>
     </div>
 </div>
 <!-- End Custom template -->
 </div>
 <!--   Core JS Files   -->
 <script src="{{asset('assets/js/core/jquery-3.7.1.min.js')}}"></script>
 <script src="{{asset('assets/js/core/popper.min.js')}}"></script>
 <script src="{{asset('assets/js/core/bootstrap.min.js')}}"></script>

 <!-- jQuery Scrollbar -->
 <script src="{{asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js')}}"></script>

 <!-- Chart JS -->
 <script src="{{asset('assets/js/plugin/chart.js/chart.min.js')}}"></script>

 <!-- jQuery Sparkline -->
 <script src="{{asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js')}}"></script>

 <!-- Chart Circle -->
 <script src="{{asset('assets/js/plugin/chart-circle/circles.min.js')}}"></script>

 <!-- Datatables -->
 <script src="{{asset('assets/js/plugin/datatables/datatables.min.js')}}"></script>

 <!-- Bootstrap Notify -->
 <script src="{{asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js')}}"></script>

 <!-- jQuery Vector Maps -->
 <script src="{{asset('assets/js/plugin/jsvectormap/jsvectormap.min.js')}}"></script>
 <script src="{{asset('assets/js/plugin/jsvectormap/world.js')}}"></script>

 <!-- Sweet Alert -->
 <script src="{{asset('assets/js/plugin/sweetalert/sweetalert.min.js')}}"></script>

 <!-- Kaiadmin JS -->
 <script src="{{asset('assets/js/kaiadmin.min.js')}}"></script>

 <!-- Kaiadmin DEMO methods, don't include it in your project! -->
 <script src="{{asset('assets/js/setting-demo.js')}}"></script>
 <script src="{{asset('assets/js/demo.js')}}"></script>
 <script>
$("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
    type: "line",
    height: "70",
    width: "100%",
    lineWidth: "2",
    lineColor: "#177dff",
    fillColor: "rgba(23, 125, 255, 0.14)",
});

$("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
    type: "line",
    height: "70",
    width: "100%",
    lineWidth: "2",
    lineColor: "#f3545d",
    fillColor: "rgba(243, 84, 93, .14)",
});

$("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
    type: "line",
    height: "70",
    width: "100%",
    lineWidth: "2",
    lineColor: "#ffa534",
    fillColor: "rgba(255, 165, 52, .14)",
});
 </script>
 <!-- <script>
    $(document).ready(function() {
        $("#basic-datatables").DataTable({});
    });
 </script> -->
 @auth
 <div class="modal fade" id="attendanceCheckInModal" tabindex="-1" aria-labelledby="attendanceCheckInModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="attendanceCheckInModalLabel">Mark today's attendance?</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">Your check-in time will be recorded for today.</div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                 <form action="{{ route('hrms.attendance.check-in') }}" method="POST" data-attendance-form>
                     @csrf
                     <button type="submit" class="btn btn-success"><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span> Confirm Check In</button>
                 </form>
             </div>
         </div>
     </div>
 </div>

 <div class="modal fade" id="attendanceCheckoutModal" tabindex="-1" aria-labelledby="attendanceCheckoutModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="attendanceCheckoutModalLabel">Are you sure you want to check out?</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">You cannot check in again today.</div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                 <form action="{{ route('hrms.attendance.check-out') }}" method="POST" data-attendance-form>
                     @csrf
                     <button type="submit" class="btn btn-warning"><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span> Confirm Check Out</button>
                 </form>
             </div>
         </div>
     </div>
 </div>
 @endauth
 <script>
 document.addEventListener('DOMContentLoaded', function () {
     loadAttendanceWidget();
 });

 function attendanceWidgetTarget() {
     return document.getElementById('attendance-widget-live') || document.getElementById('attendance-widget-container');
 }

 function disposeAttendanceWidget(target) {
     if (!target || !window.bootstrap) return;
     target.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (trigger) {
         const dropdown = bootstrap.Dropdown.getInstance(trigger);
         if (dropdown) dropdown.dispose();
     });
 }

 function loadAttendanceWidget() {
     const target = attendanceWidgetTarget();
     if (!target || !target.dataset.widgetUrl) return Promise.resolve();

     return fetch(target.dataset.widgetUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
         .then(function (response) {
             if (!response.ok) throw new Error('Attendance widget failed');
             return response.text();
         })
         .then(function (html) {
             disposeAttendanceWidget(target);
             target.insertAdjacentHTML('beforebegin', html);
             target.remove();
         })
         .catch(function () {
             if (target.id === 'attendance-widget-container') target.remove();
         });
 }

 function closeAttendanceDropdown(toggle) {
     if (!toggle || !window.bootstrap) return Promise.resolve();
     const dropdownRoot = toggle.closest('.dropdown');
     const trigger = dropdownRoot ? dropdownRoot.querySelector('[data-bs-toggle="dropdown"]') : null;
     if (!dropdownRoot || !trigger) return Promise.resolve();

     const dropdown = bootstrap.Dropdown.getOrCreateInstance(trigger);
     if (!dropdownRoot.classList.contains('show') && !dropdownRoot.querySelector('.dropdown-menu.show')) {
         return Promise.resolve();
     }

     return new Promise(function (resolve) {
         dropdownRoot.addEventListener('hidden.bs.dropdown', resolve, {once: true});
         dropdown.hide();
         window.setTimeout(resolve, 180);
     });
 }

 document.addEventListener('click', function (event) {
     const toggle = event.target.closest('[data-attendance-toggle]');
     if (!toggle) return;
     event.preventDefault();

     const modal = document.querySelector(toggle.dataset.confirmTarget);
     if (!modal || !window.bootstrap) return;

     closeAttendanceDropdown(toggle).then(function () {
         bootstrap.Modal.getOrCreateInstance(modal).show();
     });
 });

 document.addEventListener('submit', function (event) {
     const form = event.target.closest('[data-attendance-form]');
     if (!form) return;
     event.preventDefault();

     const button = form.querySelector('button[type="submit"]');
     if (!button || button.disabled) return;

     const activeModal = form.closest('.modal');
     const spinner = button.querySelector('.spinner-border');
     button.disabled = true;
     if (spinner) spinner.classList.remove('d-none');

     fetch(form.action, {
         method: form.method || 'POST',
         body: new FormData(form),
         headers: {
             'X-Requested-With': 'XMLHttpRequest',
             'Accept': 'application/json'
         }
     })
         .then(function (response) {
             return response.json().then(function (json) {
                 return {ok: response.ok, json: json};
             });
         })
         .then(function (result) {
             if (!result.ok || !result.json.success) throw new Error(result.json.message || 'Attendance request failed');
             if (activeModal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(activeModal).hide();
             return loadAttendanceWidget();
         })
         .catch(function (error) {
             alert(error.message || 'Unable to update attendance. Please try again.');
         })
         .finally(function () {
             button.disabled = false;
             if (spinner) spinner.classList.add('d-none');
         });
 });
 </script> </body>

 </html>


