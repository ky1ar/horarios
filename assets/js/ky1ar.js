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
    getUserData(newUser.data("id"), currentMonth, currentYear);
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
    getUserData(selectedUser.attr("data-id"), currentMonth, currentYear);
  });

  previousMonth.on("click", function () {
    currentMonth = currentMonth === 1 ? 12 : currentMonth - 1;
    if (currentMonth === 12) currentYear--;
    updateMonthDisplay();
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
    getUserData(selectedUser.attr("data-id"), currentMonth, currentYear);
  });

  userList.find("li").on("click", function () {
    userList.find("li").removeClass("active");
    $(this).addClass("active");
    updateUserDisplay();
    getUserSchedule($(this).data("id"), currentMonth, currentYear);
    getUserData($(this).data("id"), currentMonth, currentYear);
  });

  function formatDate(dateString) {
    const daysOfWeek = [
      "Lunes",
      "Martes",
      "Miércoles",
      "Jueves",
      "Viernes",
      "Sábado",
      "Domingo",
    ];
    const months = [
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

    const [year, month, day] = dateString.split("-");
    const date = new Date(`${year}-${month}-${day}`);
    const dayOfWeek = daysOfWeek[date.getDay()];
    const formattedDate = `${dayOfWeek} ${day} de ${
      months[parseInt(month) - 1]
    } del ${year}`;
    return formattedDate;
  }

  function showModal(stamp, date, userId) {
    $("#stampInput").val(stamp);
    $("#dateInput").val(date);
    const formattedDate = formatDate(date);
    $("#dayInput").val(formattedDate);
    $("#userIdInput").val(userId);
    $(".modal-stamp").fadeIn();
  }

  function hideModal() {
    $(".modal-stamp").fadeOut();
  }

  $(document).on("click", function (event) {
    if (
      !$(event.target).closest(".modal-content").length &&
      !$(event.target).closest(".schedule-item").length
    ) {
      hideModal();
    }
  });

  $(document).on("click", ".schedule-item", function () {
    var date = $(this).data("date");
    var userId = selectedUser.attr("data-id");
    console.log(`Fetching schedule for date: ${date}, userId: ${userId}`);
    $.ajax({
      url: "../routes/del/get_stamp.php",
      method: "POST",
      data: { userId: userId, date: date },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showModal(response.stamp, date, userId);
        } else if (response.message === "El día es un feriado") {
          console.log("No se abrió un modal por ser feriado");
        } else {
          showModal("", date, userId);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });

  $("#stampForm").on("submit", function (event) {
    event.preventDefault();
    const stamp = $("#stampInput").val();
    const date = $("#dateInput").val();
    const userId = $("#userIdInput").val();

    $.ajax({
      url: "../routes/del/update_stamp.php",
      method: "POST",
      data: { stamp: stamp, date: date, userId: userId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          hideModal();
          // Opcional: Actualizar la vista si es necesario
          getUserSchedule(userId, currentMonth, currentYear);
        } else {
          alert("Error al guardar el registro: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });

  // function calcularSumaCalcPorSemana(userId, year, month) {
  //   var currentMonth = new Date().getMonth() + 1;

  //   $(".hrr-box").each(function (index) {
  //     var $hrrBox = $(this);
  //     var semana = index + 1;
  //     var sumaHoras = 0;
  //     var sumaMinutos = 0;

  //     // Realiza la solicitud para obtener acumulado_valor_dia
  //     getWeeklyData(userId, semana, year, month, function (acumuladoValorDia) {
  //       $hrrBox.find(".calc").each(function () {
  //         var calc = $(this).text().trim();
  //         var fecha = new Date($(this).data("date"));
  //         var mesCalc = fecha.getMonth() + 1;
  //         if (mesCalc === currentMonth) {
  //           if (calc !== "DF") {
  //             var sign = calc.startsWith("-") ? -1 : 1;
  //             var tiempo = calc.replace(/[^\d:]/g, "").split(":");
  //             var horas = parseInt(tiempo[0], 10) * sign;
  //             var minutos = parseInt(tiempo[1], 10) * sign;
  //             sumaHoras += horas;
  //             sumaMinutos += minutos;
  //           }
  //         }
  //       });
  //       if (sumaMinutos >= 60) {
  //         sumaHoras += Math.floor(sumaMinutos / 60);
  //         sumaMinutos = sumaMinutos % 60;
  //       } else if (sumaMinutos <= -60) {
  //         sumaHoras += Math.ceil(sumaMinutos / 60);
  //         sumaMinutos = sumaMinutos % 60;
  //       }
  //       var resultadoHoras = sumaHoras;
  //       var resultadoMinutos = Math.abs(sumaMinutos)
  //         .toString()
  //         .padStart(2, "0");
  //       var resultado;
  //       if (sumaHoras < 0 || (sumaHoras === 0 && sumaMinutos < 0)) {
  //         resultado =
  //           "-" +
  //           Math.abs(resultadoHoras).toString().padStart(2, "0") +
  //           ":" +
  //           resultadoMinutos;
  //       } else {
  //         resultado =
  //           resultadoHoras.toString().padStart(2, "0") + ":" + resultadoMinutos;
  //       }
  //       function sumarRestarHoras(
  //         totalMinutosActual,
  //         resultado,
  //         restar = false
  //       ) {
  //         const [horas, minutos] = totalMinutosActual.split(":").map(Number);
  //         const [horas2, minutos2] = resultado.split(":").map(Number);
  //         const totalMinutos = horas * 60 + minutos;
  //         const totalminutos2 = horas2 * 60 + minutos2;
  //         const signo = restar ? -1 : 1;
  //         const nuevoTotalMinutos = totalMinutos + signo * totalminutos2;

  //         const nuevaHora = `${Math.floor(nuevoTotalMinutos / 60)}:${(
  //           nuevoTotalMinutos % 60
  //         )
  //           .toString()
  //           .padStart(2, "0")}`;
  //         return nuevaHora;
  //       }
  //       function horaAMinutos(hora) {
  //         const [horas, minutos] = hora.split(":").map(Number);
  //         return horas * 60 + minutos;
  //       }

  //       function calcularPorcentaje(tiempoInicial, resultado) {
  //         const minutosInicial = horaAMinutos(tiempoInicial);
  //         const minutosResultado = horaAMinutos(resultado);
  //         var porcentaje = (minutosResultado / minutosInicial) * 100;

  //         return porcentaje;
  //       }
  //       if (resultado.includes("-")) {
  //         const nuevaHoraResta = sumarRestarHoras(
  //           acumuladoValorDia.toString(),
  //           resultado,
  //           true
  //         );
  //         const porcentaje = calcularPorcentaje(
  //           acumuladoValorDia,
  //           nuevaHoraResta
  //         );
  //         $hrrBox
  //           .find(".minS")
  //           .text(nuevaHoraResta + "h" + " / " + acumuladoValorDia + "h");
  //         $hrrBox.find(".porT").text(porcentaje.toFixed(1) + "%");
  //       } else {
  //         const nuevaHoraSuma = sumarRestarHoras(
  //           acumuladoValorDia.toString(),
  //           resultado
  //         );
  //         const porcentaje = calcularPorcentaje(
  //           acumuladoValorDia,
  //           nuevaHoraSuma
  //         );
  //         $hrrBox
  //           .find(".minS")
  //           .text(nuevaHoraSuma + "h" + " / " + acumuladoValorDia + "h");
  //         $hrrBox.find(".porT").text(porcentaje.toFixed(1) + "%");
  //       }
  //     });
  //   });
  // }
  function calcularSumaCalcPorSemana(userId, year, month) {
    var currentMonth = new Date().getMonth() + 1;

    $(".hrr-box").each(function (index) {
      var $hrrBox = $(this);
      var semana = index + 1;
      var sumaHoras = 0;
      var sumaMinutos = 0;

      getWeeklyData(
        userId,
        semana,
        year,
        month,
        function (acumuladoValorDia, idProfile) {
          $hrrBox.find(".calc").each(function () {
            var calc = $(this).text().trim();
            var fecha = new Date($(this).data("date"));
            var diaSemana = fecha.getDay();
            var mesCalc = fecha.getMonth() + 1;
            console.log(idProfile);
            if (mesCalc === currentMonth) {
              if (calc === "DF") {
                if (idProfile === 1 && diaSemana >= 1 && diaSemana <= 5) {
                  sumaHoras -= 8;
                } else if (idProfile === 2) {
                  if (diaSemana >= 1 && diaSemana <= 5) {
                    sumaHoras -= 8;
                  } else if (diaSemana === 6) {
                    sumaHoras -= 4;
                  }
                } else if (
                  idProfile === 3 &&
                  diaSemana >= 1 &&
                  diaSemana <= 6
                ) {
                  sumaHoras -= 8;
                }
              } else {
                var sign = calc.startsWith("-") ? -1 : 1;
                var tiempo = calc.replace(/[^\d:]/g, "").split(":");
                var horas = parseInt(tiempo[0], 10) * sign;
                var minutos = parseInt(tiempo[1], 10) * sign;
                sumaHoras += horas;
                sumaMinutos += minutos;
              }
            }
          });

          if (sumaMinutos >= 60) {
            sumaHoras += Math.floor(sumaMinutos / 60);
            sumaMinutos = sumaMinutos % 60;
          } else if (sumaMinutos <= -60) {
            sumaHoras += Math.ceil(sumaMinutos / 60);
            sumaMinutos = sumaMinutos % 60;
          }
          var resultadoHoras = sumaHoras;
          var resultadoMinutos = Math.abs(sumaMinutos)
            .toString()
            .padStart(2, "0");
          var resultado;
          if (sumaHoras < 0 || (sumaHoras === 0 && sumaMinutos < 0)) {
            resultado =
              "-" +
              Math.abs(resultadoHoras).toString().padStart(2, "0") +
              ":" +
              resultadoMinutos;
          } else {
            resultado =
              resultadoHoras.toString().padStart(2, "0") +
              ":" +
              resultadoMinutos;
          }

          function sumarRestarHoras(
            totalMinutosActual,
            resultado,
            restar = false
          ) {
            const [horas, minutos] = totalMinutosActual.split(":").map(Number);
            const [horas2, minutos2] = resultado.split(":").map(Number);
            const totalMinutos = horas * 60 + minutos;
            const totalminutos2 = horas2 * 60 + minutos2;
            const signo = restar ? -1 : 1;
            const nuevoTotalMinutos = totalMinutos + signo * totalminutos2;

            const nuevaHora = `${Math.floor(nuevoTotalMinutos / 60)}:${(
              nuevoTotalMinutos % 60
            )
              .toString()
              .padStart(2, "0")}`;
            return nuevaHora;
          }

          function horaAMinutos(hora) {
            const [horas, minutos] = hora.split(":").map(Number);
            return horas * 60 + minutos;
          }

          function calcularPorcentaje(tiempoInicial, resultado) {
            const minutosInicial = horaAMinutos(tiempoInicial);
            const minutosResultado = horaAMinutos(resultado);
            var porcentaje = (minutosResultado / minutosInicial) * 100;

            return porcentaje;
          }

          if (resultado.includes("-")) {
            const nuevaHoraResta = sumarRestarHoras(
              acumuladoValorDia.toString(),
              resultado,
              true
            );
            const porcentaje = calcularPorcentaje(
              acumuladoValorDia,
              nuevaHoraResta
            );
            $hrrBox
              .find(".minS")
              .text(nuevaHoraResta + "h" + " / " + acumuladoValorDia + "h");
            $hrrBox.find(".porT").text(porcentaje.toFixed(1) + "%");
          } else {
            const nuevaHoraSuma = sumarRestarHoras(
              acumuladoValorDia.toString(),
              resultado
            );
            const porcentaje = calcularPorcentaje(
              acumuladoValorDia,
              nuevaHoraSuma
            );
            $hrrBox
              .find(".minS")
              .text(nuevaHoraSuma + "h" + " / " + acumuladoValorDia + "h");
            $hrrBox.find(".porT").text(porcentaje.toFixed(1) + "%");
          }
        }
      );
    });
  }

  function getWeeklyData(userId, week, year, month, callback) {
    $.ajax({
      url: "../routes/del/get_week.php",
      method: "POST",
      data: { userId: userId, week: week, year: year, month: month },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          if (response.data.length > 0) {
            var acumuladoValorDia = response.data[0].acumulado_valor_dia;
            var idProfile = response.data[0].id_profile; // Asegúrate de pasar idProfile
            callback(acumuladoValorDia, idProfile);
          } else {
            console.error("No se encontraron datos en la respuesta");
          }
        } else {
          console.error(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }

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

              // Añadir el bloque HTML data-sem
              $(
                "<div class='data-sem'>" +
                  "<p class='porT'></p>" +
                  "<p class='minS'></p>" +
                  "</div>"
              ).appendTo($currentHrrBox);

              $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
              currentWeek++;
            }

            var $hrrDay = $currentHrrBox.find(".hrr-day");
            var $dayList = $(
              "<ul class='schedule-item' data-date='" +
                entry.calendar_date +
                "'></ul>"
            ).appendTo($hrrDay);

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

            if (entry.holiday != 1) {
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
            }

            daysCounter++;
          });
          calcularSumaCalcPorSemana(userId, year, month);
        } else {
          console.error(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }
  function getUserData(userId, month, year) {
    var formData = new FormData();
    formData.append("userId", userId);
    formData.append("month", month);
    formData.append("year", year);
    $.ajax({
      url: "../routes/del/get_info_user.php",
      method: "POST",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (response) {
        var data = response;
        $("#totalHours").text(data.total_hours_required + " h");
        $("#totalMissingPoints").text(data.total_missing_points);
        $("#totalLatePoints").text(data.total_late_points);
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

  getUserData(selectedUser.attr("data-id"), currentMonth, currentYear);
  getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
});
