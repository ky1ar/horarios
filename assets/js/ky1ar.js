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

  document.getElementById("fileInput").addEventListener("change", function () {
    const label = document.querySelector('label[for="fileInput"]');
    if (this.files.length > 0) {
      label.textContent = this.files[0].name;
    } else {
      label.textContent = "Cargar Registro";
    }
  });

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

  function showModal(stamp, just, date, userId) {
    $("#stampInput").val(stamp);
    $("#justNameInput").val(just);
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

  // $(document).on("click", ".schedule-item", function () {
  //   var date = $(this).data("date");
  //   var userId = selectedUser.attr("data-id");
  //   // console.log(`Fetching schedule for date: ${date}, userId: ${userId}`);
  //   $.ajax({
  //     url: "../routes/del/get_stamp.php",
  //     method: "POST",
  //     data: { userId: userId, date: date },
  //     dataType: "json",
  //     success: function (response) {
  //       if (response.success) {
  //         console.log(response.just);
  //         showModal(response.stamp, date, userId);
  //       } else if (response.message === "El día es un feriado") {
  //         console.log("No se abrió un modal por ser feriado");
  //       } else {
  //         showModal("", date, userId);
  //       }
  //     },
  //     error: function (xhr, status, error) {
  //       console.error("Error en la solicitud AJAX:", error);
  //     },
  //   });
  // });

  // $("#stampForm").on("submit", function (event) {
  //   event.preventDefault();
  //   const stamp = $("#stampInput").val();
  //   const date = $("#dateInput").val();
  //   const userId = $("#userIdInput").val();

  //   $.ajax({
  //     url: "../routes/del/update_stamp.php",
  //     method: "POST",
  //     data: { stamp: stamp, date: date, userId: userId },
  //     dataType: "json",
  //     success: function (response) {
  //       if (response.success) {
  //         hideModal();
  //         // Opcional: Actualizar la vista si es necesario
  //         getUserSchedule(userId, currentMonth, currentYear);
  //       } else {
  //         alert("Error al guardar el registro: " + response.message);
  //       }
  //     },
  //     error: function (xhr, status, error) {
  //       console.error("Error en la solicitud AJAX:", error);
  //     },
  //   });
  // });

  $(document).on("click", ".calc", function () {
    var date = $(this).data("date");
    var userId = selectedUser.attr("data-id");
    $.ajax({
      url: "../routes/del/get_stamp.php",
      method: "POST",
      data: { userId: userId, date: date },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          console.log(response.just);
          showModal(response.stamp, response.just, date, userId);
        } else if (response.message === "El día es un feriado") {
          console.log("No se abrió un modal por ser feriado");
        } else {
          showModal("", "", date, userId);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });
  $("#stampForm").on("submit", function (event) {
    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      url: "../routes/del/update_stamp.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          hideModal();
          // Suponiendo que userId, currentMonth y currentYear están disponibles
          getUserSchedule(formData.get("userId"), currentMonth, currentYear);
          location.reload(true); // Recargar la página y borrar la caché
        } else {
          alert("Error al guardar el registro: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });

  function calcularSumaCalcPorSemana(userId, year, month) {
    var currentMonth = new Date().getMonth() + 1;

    $(".hrr-box").each(function (index) {
      var $hrrBox = $(this);
      var semana = index + 1;
      var sumaHoras = 0;
      var sumaMinutos = 0;
      var dfCount = 0;
      var dfDates = [];
      let final = 0;
      getWeeklyData(
        userId,
        semana,
        year,
        month,
        function (acumuladoValorDia, idProfile) {
          // console.log('*********************************');
          $hrrBox.find(".calc").each(function () {
            // console.log('---------------------');
            var calc = $(this).text().trim();
            const dayname = $(this).closest("ul").find("li").first().text();

            var fecha = new Date($(this).data("date") + "T00:00:00");

            var mesCalc = fecha.getMonth() + 1;

            if (mesCalc === currentMonth) {
              if (calc !== "DF") {
                // console.log('old calc ',calc);
                if (calc.startsWith("-")) {
                  const tiempo = calc.replace(/[^\d:]/g, "").split(":");
                  const horas = parseInt(tiempo[0], 10);
                  const minutos = parseInt(tiempo[1], 10);

                  const total = horas * 60 + minutos;
                  // console.log('total ',total);
                  const fixed = 8 * 60;
                  let newc = fixed - total;
                  final += newc;
                  const nhours = Math.floor(newc / 60);
                  const nminutos = newc % 60;
                  const formattedMinutes = String(nminutos).padStart(2, "0");
                  // console.log('new calc ',nhours+':'+formattedMinutes);
                } else {
                  //console.log('idProfile ',idProfile);
                  let fixed;
                  // console.log('dayname ',dayname);
                  if (dayname.includes("Sáb")) {
                    if (idProfile == 1) {
                      fixed = 0;
                    } else if (idProfile == 2) {
                      fixed = 4 * 60;
                    } else {
                      fixed = 8 * 60;
                    }
                  } else {
                    fixed = 8 * 60;
                  }

                  const tiempo = calc.replace(/[^\d:]/g, "").split(":");
                  const horas = parseInt(tiempo[0], 10);
                  const minutos = parseInt(tiempo[1], 10);
                  const total = horas * 60 + minutos;

                  let newc = fixed + total;
                  final += newc;
                  const nhours = Math.floor(newc / 60);
                  const nminutos = newc % 60;
                  const formattedMinutes = String(nminutos).padStart(2, "0");
                  // console.log('new calc ',nhours+':'+formattedMinutes);
                }

                var sign = calc.startsWith("-") ? -1 : 1;

                var tiempo = calc.replace(/[^\d:]/g, "").split(":");
                var horas = parseInt(tiempo[0], 10) * sign;
                var minutos = parseInt(tiempo[1], 10) * sign;
                // console.log('horas ',horas);
                // console.log('minutos ',minutos);

                sumaHoras += horas;
                sumaMinutos += minutos;
              } else {
                dfCount++;
                dfDates.push(fecha);
              }
            }
          });

          //console.log('sumaHoras ',sumaHoras);
          //console.log('sumaMinutos ',sumaMinutos);

          // dfDates.forEach(function (dfDate) {
          //   var restaHoras = 0;
          //   if (idProfile === 1) {
          //     if (dfDate.getDay() >= 1 && dfDate.getDay() <= 5) {
          //       restaHoras = 8;
          //     }
          //   } else if (idProfile === 2) {
          //     if (dfDate.getDay() >= 1 && dfDate.getDay() <= 5) {
          //       restaHoras = 8;
          //     } else if (dfDate.getDay() === 6) {
          //       restaHoras = 4;
          //     }
          //   } else if (idProfile === 3) {
          //     restaHoras = 8;
          //   }
          //   sumaHoras -= restaHoras;
          // });

          // if (sumaMinutos >= 60) {
          //   sumaHoras += Math.floor(sumaMinutos / 60);
          //   sumaMinutos = sumaMinutos % 60;
          // } else if (sumaMinutos <= -60) {
          //   sumaHoras += Math.ceil(sumaMinutos / 60);
          //   sumaMinutos = sumaMinutos % 60;
          // }

          // var resultadoHoras = sumaHoras;
          // var resultadoMinutos = Math.abs(sumaMinutos)
          //   .toString()
          //   .padStart(2, "0");
          // var resultado;

          // if (sumaHoras < 0 || (sumaHoras === 0 && sumaMinutos < 0)) {
          //   // console.log('a',resultadoHoras,resultadoMinutos);
          //   resultado =
          //     "-" +
          //     Math.abs(resultadoHoras).toString().padStart(2, "0") +
          //     ":" +
          //     resultadoMinutos;
          // } else {
          //   // console.log('b',resultadoHoras,resultadoMinutos);
          //   resultado =
          //     resultadoHoras.toString().padStart(2, "0") +
          //     ":" +
          //     resultadoMinutos;
          // }
          // function sumarRestarHoras(
          //   totalMinutosActual,
          //   resultado,
          //   restar = false
          // ) {
          //   const [horas, minutos] = totalMinutosActual.split(":").map(Number);
          //   const totalMinutos = horas * 60 + minutos;

          //   let [horas2, minutos2] = resultado.split(":").map(Number);
          //   const signo = resultado.startsWith("-") ? -1 : 1;
          //   const totalMinutos2 =
          //     signo * (Math.abs(horas2) * 60 + Math.abs(minutos2));

          //   let nuevoTotalMinutos;
          //   if (restar) {
          //     nuevoTotalMinutos = totalMinutos - totalMinutos2;
          //   } else {
          //     nuevoTotalMinutos = totalMinutos + totalMinutos2;
          //   }
          //   if (nuevoTotalMinutos < 0) {
          //     nuevoTotalMinutos = 0;
          //   }
          //   const nuevasHoras = Math.floor(nuevoTotalMinutos / 60);
          //   const nuevosMinutos = nuevoTotalMinutos % 60;
          //   const resultadoFinal = `${nuevasHoras
          //     .toString()
          //     .padStart(2, "0")}:${nuevosMinutos.toString().padStart(2, "0")}`;
          //   return resultadoFinal;
          // }

          // // Función para convertir hora a minutos
          // function horaAMinutos(hora) {
          //   const [horas, minutos] = hora.split(":").map(Number);
          //   return horas * 60 + minutos;
          // }

          // const nhours = Math.floor(final / 60);
          // const nminutos = final % 60;
          // const formattedMinutes = String(nminutos).padStart(2, '0');
          // //console.log('new calc ',nhours+':'+formattedMinutes);

          // $hrrBox
          //     .find(".minS")
          //     .text(nhours+':'+formattedMinutes + "h" + " / " + acumuladoValorDia + "h");
          const nhours = Math.floor(final / 60);
          const nminutos = final % 60;
          const formattedMinutes = String(nminutos).padStart(2, "0");
          const time1 = nhours + ":" + formattedMinutes;
          const time2 = acumuladoValorDia;

          // Funciones de utilidad
          function timeToMinutes(time) {
            const [hours, minutes] = time.split(":").map(Number);
            return hours * 60 + minutes;
          }

          function calculatePercentage(time1, time2) {
            const minutes1 = timeToMinutes(time1);
            const minutes2 = timeToMinutes(time2);
            return (minutes1 / minutes2) * 100;
          }

          // Calcular el porcentaje
          const percentage = calculatePercentage(time1, time2);

          // Actualizar el HTML
          $hrrBox.find(".minS").text(time1 + "h" + " / " + time2 + "h");
          $hrrBox.find(".porT").text(percentage.toFixed(1) + "%");
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
            var idProfile = response.data[0].id_profile;
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
    $.ajax({
      url: "../routes/del/get_user_schedule.php",
      method: "POST",
      data: { userId: userId, month: month, year: year },
      dataType: "json",
      success: function (response) {
        //console.log(response.schedule);
        if (response.success) {
          $(".ky1-hrr").empty();
          var daysCounter = 0;
          var $currentHrrBox;
          var currentWeek = 1;
          response.schedule.forEach(function (entry, index) {
            var dayName = entry.day_of_week_es;
            var dayNumber = entry.day_number;
            var hPoints = entry.time_difference;

            console.log("Este es el just: " + entry.just);
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
              stamps.forEach(function (stamp, stampIndex) {
                for (var i = 0; i < stamp.length; i += 5) {
                  var timeSlot = stamp.slice(i, i + 5);
                  var $li = $("<li>" + timeSlot + "</li>");
                  if (stampIndex === 0 && i === 0 && timeSlot > "09:00") {
                    $li.css("color", "red");
                  }
                  $li.appendTo($dayList);
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
                $calcLi.addClass("df");
              } else if (hPoints.startsWith("-")) {
                $calcLi.addClass("minus");
              } else {
                $calcLi.addClass("plus");
              }

              $calcLi.appendTo($dayList);
              if (entry.just && entry.just.trim() !== '') {
                // Insertar el elemento li solo si entry.just no está vacío
                $("<li class='justDoc'><img src='./assets/img/doc.png' alt=''></li>").appendTo($dayList);
              }
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
  // Controlador de eventos para el clic en el elemento li con clase justDoc
$(document).on("click", "li.justDoc", function () {
  // Obtener el nombre del archivo de la justificación del servidor
  var imageName = entry.just;

  // Construir la ruta completa de la imagen en el servidor
  var imageUrl = "/justs/" + imageName;

  // Actualizar el atributo src del elemento img en el modal
  $("#justificationImage").attr("src", imageUrl);

  // Mostrar el modal
  $(".viewDoc").show();
});

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
        var minutesLate =
          parseInt(data.total_minutes_late_formatted.split(":")[0]) * 60 +
          parseInt(data.total_minutes_late_formatted.split(":")[1]);
        var onePercentHours =
          parseInt(data.one_percent_total_hours.split(":")[0]) * 60 +
          parseInt(data.one_percent_total_hours.split(":")[1]);
        var difference = minutesLate - onePercentHours;

        // Multiplicar la diferencia por 1.2 y redondear
        var differenceAdjusted = Math.max(0, difference) * 1.2;
        differenceAdjusted = Math.round(differenceAdjusted); // Redondear

        var hoursDifference = Math.floor(differenceAdjusted / 60);
        var minutesDifference = differenceAdjusted % 60;
        var differenceFormatted =
          (hoursDifference < 10 ? "0" : "") +
          hoursDifference +
          ":" +
          (minutesDifference < 10 ? "0" : "") +
          minutesDifference;

        var adjustedHours =
          parseInt(data.adjusted_hours.split(":")[0]) * 60 +
          parseInt(data.adjusted_hours.split(":")[1]);

        // Sumar la diferencia ajustada a adjusted_hours
        var sum = adjustedHours + differenceAdjusted;
        var sumHours = Math.floor(sum / 60);
        var sumMinutes = sum % 60;
        var sumFormatted =
          (sumHours < 10 ? "0" : "") +
          sumHours +
          ":" +
          (sumMinutes < 10 ? "0" : "") +
          sumMinutes;

        $("#totalHours").text(sumFormatted + " h");
        $("#totalMissingPoints").text(data.total_missing_points);
        $("#totalLatePoints").text(data.total_late_points);
        $("#tolerancia").text(
          data.total_minutes_late_formatted +
            "h" +
            " / " +
            data.one_percent_total_hours +
            "h"
        );
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
