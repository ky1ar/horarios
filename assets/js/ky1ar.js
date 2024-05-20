$(document).ready(function () {
  const nextUser = $("#nextUser");
  const previousUser = $("#previousUser");
  const userList = $("#userList");
  const nextMonth = $("#nextMonth");
  const previousMonth = $("#previousMonth");

  const selectedUser = $("#selectedUser");
  const userName = $("#userName");
  const userCategory = $("#userCategory");

  const userImage = $("#userImage");
  const imagePath = "assets/img/profiles/";

  const monthNames = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];
  let currentMonth = new Date().getMonth() + 1; // Mes actual (1 a 12)
  let currentYear = new Date().getFullYear(); // Año actual

  function updateMonthDisplay() {
    $(".ky1-dte span").text(`${monthNames[currentMonth - 1]}, ${currentYear}`);
  }

  function updateUserDisplay() {
    const activeUser = userList.find(".active");
    selectedUser.attr("data-id", activeUser.data("id"));
    userImage.attr("src", imagePath + activeUser.data("slug") + ".png");
    userName.text(activeUser.data("name"));
    userCategory.text(activeUser.data("category"));
  }

  function updateUser(offset) {
    let current = userList.find(".active").index();
    let total = userList.find("li").length - 1;
    userList.find("li").removeClass("active");

    current = current + offset;
    if (offset == 1) {
      if (current > total) current = 0;
    } else if (offset == -1) {
      if (current < 0) current = total;
    }

    let newUser = userList.find("li").eq(current);
    newUser.addClass("active");
    updateUserDisplay();
    getUserSchedule(newUser.data("id"), currentMonth, currentYear); 
    // loadUserSchedule(newUser.data("id"), currentMonth, currentYear);
  }

  nextUser.on("click", function () {
    updateUser(1);
  });
  previousUser.on("click", function () {
    updateUser(-1);
  });

  nextMonth.on("click", function () {
    currentMonth = (currentMonth % 12) + 1;
    if (currentMonth === 1) currentYear++;
    updateMonthDisplay();
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); 
    // loadUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
  });

  previousMonth.on("click", function () {
    currentMonth = currentMonth === 1 ? 12 : currentMonth - 1;
    if (currentMonth === 12) currentYear--;
    updateMonthDisplay();
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); 
    // loadUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
  });

  userList.find("li").on("click", function () {
    userList.find("li").removeClass("active");
    $(this).addClass("active");
    updateUserDisplay();
    getUserSchedule($(this).data("id"), currentMonth, currentYear);
    // loadUserSchedule($(this).data("id"), currentMonth, currentYear); 
  });

  // function loadUserSchedule(userId, month, year) {
  //   $.ajax({
  //     url: "../routes/del/get_info_user.php",
  //     type: "POST",
  //     data: {
  //       userId: userId,
  //       month: month,
  //       year: year,
  //     },
  //     cache: false,
  //     contentType: false,
  //     processData: false,
  //     dataType: "json",
  //     success: function (response) {
  //       if (response.success) {
  //         var data = response.data[0];
  //         $("#totalHours").text(data.total_hours_required + " h");
  //         $("#totalMissingPoints").text(data.total_sin_registro);
  //         $("#totalLatePoints").text(data.total_tardanzas);
  //         $("#totalUnjustifiedAbsences").text(data.total_faltas_injustificadas);
  //       } else {
  //         alert("Error: " + response.message);
  //       }
  //     },
  //     error: function (xhr, status, error) {
  //       alert("Error: " + error);
  //     },
  //   });
  // }

  function getUserSchedule(userId, month, year) {
    console.log(
      `Fetching schedule for userId: ${userId}, month: ${month}, year: ${year}`
    ); 
    $.ajax({
      url: "../routes/del/get_user_schedule.php",
      method: "POST",
      data: { userId: userId, month: month, year: year },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $(".ky1-hrr").empty();
          var daysCounter = 0;
          var $currentHrrBox;
          var currentWeek = 1;
          response.schedule.forEach(function (entry, index) {
            var dayName = entry.day_of_week_es;
            var dayNumber = entry.day_number;
            var hPoints = entry.time_difference;
            if (dayName.toLowerCase() === "domingo") {
              return;
            }

            if (dayName.toLowerCase() === "lunes" || index === 0) {
              $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(
                ".ky1-hrr"
              );
              $("<span>Semana " + currentWeek + "</span>").appendTo(
                $currentHrrBox
              );
              $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
              currentWeek++; 
            }

            var $hrrDay = $currentHrrBox.find(".hrr-day");
            var $dayList = $("<ul></ul>").appendTo($hrrDay);

            $(
              "<li class='day-nam'>" +
                dayName.substring(0, 3) +
                " " +
                dayNumber +
                "</li>"
            ).appendTo($dayList);

            if (entry.holiday == 1) {
              $("<li class='test'>FERIADO</li>").appendTo($dayList);
            } else if (entry.stamp) {
              // Verifica si hay datos de estampas
              var stamps = entry.stamp.split(",");
              stamps.forEach(function (stamp) {
                for (var i = 0; i < stamp.length; i += 5) {
                  $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo(
                    $dayList
                  );
                }
              });
            } else {
              $("<li></li>").appendTo($dayList);
            }

            var $calcLi = $(
              "<li class='calc' data-date='" +
                entry.calendar_date +
                "'>" +
                hPoints +
                "</li>"
            );

            if (hPoints === "DF") {
              $calcLi.css("box-shadow", "inset 0 -4rem 0 0 #F0DD38");
            } else if (hPoints.startsWith("+")) {
              $calcLi.css("box-shadow", "inset 0 -4rem 0 0 #0baa75");
            } else if (hPoints.startsWith("-")) {
              $calcLi.css("box-shadow", "inset 0 -4rem 0 0 #DE0B0B");
            }

            $calcLi.appendTo($dayList);

            daysCounter++;
          });

          console.log(response.schedule); 
        } else {
          console.error(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }

  updateMonthDisplay();
  if (userList.find(".active").length === 0) {
    userList.find("li").first().addClass("active");
  }
  updateUserDisplay();
  getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); 
});
