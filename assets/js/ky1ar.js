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
  let currentMonth = new Date().getMonth() + 1;
  let currentYear = new Date().getFullYear();

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
  function updateUserData(
    totalHours,
    totalMissingPoints,
    totalLatePoints,
    totalUnjustifiedAbsences
  ) {
    $("#totalHours").text(totalHours + " h");
    $("#totalMissingPoints").text(totalMissingPoints);
    $("#totalLatePoints").text(totalLatePoints);
    $("#totalUnjustifiedAbsences").text(totalUnjustifiedAbsences);
  }
  function getUserData(userId, month, year) {
    console.log(
      `Data: ${userId}, month: ${month}, year: ${year}`
    ); // Depuración
    $.ajax({
      url: "../routes/del/get_info_user.php", // Actualiza la ruta a tu archivo PHP
      method: "POST",
      data: { userId: userId, month: month, year: year },
      dataType: "json",
      success: function (response) {
        console.log("Datos recibidos del servidor:", response); // Agrega un console.log aquí
        if (response.success) {
          const userData = response.data[0]; // Se asume que el servidor devuelve un solo conjunto de datos
          updateUserData(userData.total_hours_required, userData.total_sin_registro, userData.total_tardanzas, userData.total_faltas_injustificadas);
        } else {
          console.error(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }
  
  getUserData(selectedUser.attr("data-id"), currentMonth, currentYear);

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
  });

  previousMonth.on("click", function () {
    currentMonth = currentMonth === 1 ? 12 : currentMonth - 1;
    if (currentMonth === 12) currentYear--;
    updateMonthDisplay();
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
  });

  userList.find("li").on("click", function () {
    userList.find("li").removeClass("active");
    $(this).addClass("active");
    updateUserDisplay();
    getUserSchedule($(this).data("id"), currentMonth, currentYear);
  });

  function getUserSchedule(userId, month, year) {
    console.log(
      `Fetching schedule for userId: ${userId}, month: ${month}, year: ${year}`
    ); // Depuración
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
              currentWeek++; // Aumenta el contador de semana
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
              // Si es un feriado, muestra "FERIADO" en una sola línea
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
