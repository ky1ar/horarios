$(document).ready(function () {
  const nextMonth = $("#nextMonth");
  const previousMonth = $("#previousMonth");
  const selectedUser = $("#selectedUser");
  const userImage = $("#userImage");
  const userName = $("#userName");
  const userCategory = $("#userCategory");
  const userList = $("#userList");

  const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
  let currentMonth = new Date().getMonth() + 1; // Mes actual (1 a 12)

  function updateMonthDisplay() {
    $(".ky1-dte span").text(`${monthNames[currentMonth - 1]}, 2024`);
  }

  function getUserSchedule(userId) {
    $.ajax({
      url: "../routes/del/get_user_schedule.php",
      method: "POST",
      data: { userId: userId, month: currentMonth },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $(".ky1-hrr").empty();

          var daysCounter = 0;
          var $currentHrrBox;
          var currentWeek = 1;
          response.schedule.forEach(function (entry, index) {
            var dayName = entry.day_name_espanol;
            var dayNumber = entry.day_number;

            if (dayName.toLowerCase() === "domingo") {
              return;
            }

            if (dayName.toLowerCase() === "lunes" || index === 0) {
              $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
              $("<span>Semana " + currentWeek + "</span>").appendTo($currentHrrBox);
              $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
              currentWeek++;
            }

            var $hrrDay = $currentHrrBox.find('.hrr-day');
            var $dayList = $("<ul></ul>").appendTo($hrrDay);

            $("<li class='day-nam'>" + dayName.substring(0, 3) + " " + dayNumber + "</li>").appendTo($dayList);

            if (entry.stamp) {
              var stamps = entry.stamp.split(",");
              stamps.forEach(function (stamp) {
                for (var i = 0; i < stamp.length; i += 5) {
                  $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo($dayList);
                }
              });
            } else {
              $("<li></li>").appendTo($dayList);
            }

            daysCounter++;
          });

          console.log(response.schedule);
        } else {
          console.error(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      }
    });
  }

  nextMonth.on("click", function () {
    currentMonth = (currentMonth % 12) + 1;
    updateMonthDisplay();
    getUserSchedule(selectedUser.data("id"));
  });

  previousMonth.on("click", function () {
    currentMonth = (currentMonth === 1) ? 12 : currentMonth - 1;
    updateMonthDisplay();
    getUserSchedule(selectedUser.data("id"));
  });

  updateMonthDisplay();

  userList.find("li").on("click", function () {
    let $this = $(this);
    userList.find("li").removeClass("active");
    $this.addClass("active");

    selectedUser.attr("data-id", $this.data("id"));
    userImage.attr("src", "assets/img/profiles/" + $this.data("slug") + ".png");
    userName.text($this.data("name"));
    userCategory.text($this.data("category"));

    getUserSchedule($this.data("id"));
  });

  // Cargar horario del usuario activo al inicio
  getUserSchedule(selectedUser.data("id"));
});
