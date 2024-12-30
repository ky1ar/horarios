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

  $(".ky1-permisos button").click(function () {
    $(".ky1-permisos .desc").fadeIn();
    $(".ky1-permisos .fond").fadeIn();
  });
  $(".ky1-permisos .fond").click(function (event) {
    if ($(event.target).hasClass("fond")) {
      $(".ky1-permisos .desc").fadeOut();
      $(this).fadeOut();
    }
  });

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
    getStampSpecial(newUser.data("id"), currentMonth, currentYear);
    getLastDayTime(newUser.data("id"), currentMonth, currentYear);
    getUserComments(newUser.data("id"));
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

    getStampSpecial(selectedUser.attr("data-id"), currentMonth, currentYear);
    getLastDayTime(selectedUser.attr("data-id"), currentMonth, currentYear);
    getUserComments(selectedUser.attr("data-id"));
  });

  previousMonth.on("click", function () {
    currentMonth = currentMonth === 1 ? 12 : currentMonth - 1;
    if (currentMonth === 12) currentYear--;
    updateMonthDisplay();
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
    getUserData(selectedUser.attr("data-id"), currentMonth, currentYear);

    getStampSpecial(selectedUser.attr("data-id"), currentMonth, currentYear);
    getLastDayTime(selectedUser.attr("data-id"), currentMonth, currentYear);
    getUserComments(selectedUser.attr("data-id"));
  });

  userList.find("li").on("click", function () {
    userList.find("li").removeClass("active");
    $(this).addClass("active");
    updateUserDisplay();
    getUserSchedule($(this).data("id"), currentMonth, currentYear);
    getUserData($(this).data("id"), currentMonth, currentYear);

    getStampSpecial($(this).data("id"), currentMonth, currentYear);
    getLastDayTime($(this).data("id"), currentMonth, currentYear);
    getUserComments($(this).data("id"));
  });

  const lastUpdatedUserId = getCookie("lastUpdatedUserId");
  if (lastUpdatedUserId) {
    selectUserById(lastUpdatedUserId);
  }

  function getCookie(name) {
    const match = document.cookie.match(
      new RegExp("(^| )" + name + "=([^;]+)")
    );
    if (match) {
      return match[2];
    }
  }
  function selectUserById(userId) {
    userList.find("li").removeClass("active");
    const userToSelect = userList.find(`li[data-id="${userId}"]`);
    userToSelect.addClass("active");
    updateUserDisplay();
    getUserSchedule(userId, currentMonth, currentYear);
    getUserData(userId, currentMonth, currentYear);

    getStampSpecial(userId, currentMonth, currentYear);
    getLastDayTime(userId, currentMonth, currentYear);
    getUserComments(userId);
  }

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

  function showModal(stamp, just, coment, midTime, fullTime, date, userId) {
    $("#stampInput").val(stamp);
    $("#comentInput").val(coment);
    $("#justNameInput").val(just);
    $("#dateInput").val(date);

    const formattedDate = formatDate(date);
    $("#dayInput").val(formattedDate);
    $("#userIdInput").val(userId);
    if (midTime === 1) {
      $("#check1").prop("checked", true);
    } else {
      $("#check1").prop("checked", false);
    }
    if (fullTime === 1) {
      $("#check2").prop("checked", true);
    } else {
      $("#check2").prop("checked", false);
    }
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
          showModal(
            response.stamp,
            response.just,
            response.coment,
            response.mid_time,
            response.full_time,
            date,
            userId
          );
          console.log("dta: " + response.stamp);
        } else if (response.message === "El día es un feriado") {
        } else {
          showModal("", "", date, userId);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });

  let calcDiffGlobal;
  $("#stampForm").on("submit", function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    var check1 = $("#check1").prop("checked");
    var check2 = $("#check2").prop("checked");
    var mid_time = check1 ? 1 : 0;
    var full_time = check2 ? 1 : 0;
    formData.append("mid_time", mid_time);
    formData.append("full_time", full_time);
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
          calcDiffGlobal = response.calcDiff;
          getUserSchedule(formData.get("userId"), currentMonth, currentYear);
          location.reload(true);
        } else {
          alert("Error al guardar el registro: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });

  let totalMonthlyTime = "";
  function getStampSpecial(userId, month, year) {
    $.ajax({
      url: "../routes/del/dayBeforeMonth.php",
      method: "POST",
      data: { userId: userId, month: month, year: year },
      dataType: "json",
      success: function (response) {
        var calculatedTime = response.calculated_time;
        totalMonthlyTime = calculatedTime;
        first = response.date
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }

  let lastDayTime = "";
  function getLastDayTime(userId, month, year) {
    $.ajax({
      url: "../routes/del/dayUltimateMonth.php",
      method: "POST",
      data: { userId: userId, month: month, year: year },
      dataType: "json",
      success: function (response) {
        lastDayTime = response.calculated_time;
        lastday = response.date
        console.log("ultimo: " + lastday);
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }

  let globalTotalMonthlyTimeNuev = "";
  function calcularSumaCalcPorSemana(userId, year, month) {
    var totalHoursMinutes = 0;
    $(".hrr-box").each(function (index) {
      var $hrrBox = $(this);
      var semana = index + 1;
      getWeeklyData(
        userId,
        semana,
        year,
        month,
        function (acumuladoValorDia, idProfile) {
          var final = 0;
          $hrrBox.find(".calc").each(function () {
            var calc = $(this).text().trim();
            const dayname = $(this).closest("ul").find("li").first().text();
            var fecha = new Date($(this).data("date") + "T00:00:00");
            var mesCalc = fecha.getMonth() + 1;

            if (mesCalc === currentMonth) {
              if (calc !== "DF") {
                let fixed;
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
                let newc;

                if (calc.startsWith("-")) {
                  newc = fixed - total;
                } else {
                  newc = total + fixed;
                }

                final += newc;
              }
            }
          });

          const nhours = Math.floor(final / 60);
          const nminutos = final % 60;
          const formattedMinutes = String(nminutos).padStart(2, "0");
          const formattedHours = nhours.toString().padStart(2, "0");
          const time1 = formattedHours + ":" + formattedMinutes;
          const time2 = acumuladoValorDia;
          function timeToMinutes(time) {
            const [hours, minutes] = time.split(":").map(Number);
            return hours * 60 + minutes;
          }
          totalHoursMinutes += timeToMinutes(time1);
          function calculatePercentage(time1, time2) {
            const minutes1 = timeToMinutes(time1);
            const minutes2 = timeToMinutes(time2);
            return (minutes1 / minutes2) * 100;
          }
          const percentage = calculatePercentage(time1, time2);

          $hrrBox.find(".minS").text(time1 + "h" + " / " + time2 + "h");
          console.log(time1 + " " + time2);
          $hrrBox.find(".porT").text(percentage.toFixed(1) + "%");
        }
      );
    });

    $(document).ajaxStop(function () {
      const totalHours = Math.floor(totalHoursMinutes / 60);
      const totalMinutes = totalHoursMinutes % 60;
      let totalMonthlyMinutes = 0;
      if (
        totalMonthlyTime &&
        totalMonthlyTime !== "DF" &&
        totalMonthlyTime !== "" &&
        totalMonthlyTime !== null
      ) {
        const [monthlyHoursStr, monthlyMinutesStr] =
          totalMonthlyTime.split(":");
        const monthlyHours = parseInt(monthlyHoursStr, 10);
        const monthlyMinutes = parseInt(monthlyMinutesStr, 10);
        totalMonthlyMinutes = monthlyHours * 60 + monthlyMinutes;
      }
      let lastDayMinutes = 0;
      if (
        lastDayTime &&
        lastDayTime !== "DF" &&
        lastDayTime !== "" &&
        lastDayTime !== null
      ) {
        const [lastDayHoursStr, lastDayMinutesStr] = lastDayTime.split(":");
        const lastDayHours = parseInt(lastDayHoursStr, 10);
        const lastDayMinutesPart = parseInt(lastDayMinutesStr, 10);
        lastDayMinutes = lastDayHours * 60 + lastDayMinutesPart;
      }

      const newTotalMinutes =
        totalMonthlyMinutes + totalHours * 60 + totalMinutes - lastDayMinutes;
      const newHours = Math.floor(newTotalMinutes / 60);
      const newMinutes = newTotalMinutes % 60;
      const newFormattedTotalTime = `${newHours
        .toString()
        .padStart(2, "0")}:${newMinutes.toString().padStart(2, "0")}`;

      globalTotalMonthlyTimeNuev = newFormattedTotalTime;
      $(document).off("ajaxStop");
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
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", {
          status: status,
          error: error,
          responseText: xhr.responseText,
        });
      },
    });
  }

  function getMonthWithoutLeadingZero(dateString) {
    const date = new Date(dateString);
    const month = date.getMonth() + 1;
    return month.toString();
  }

  function getUserSchedule(userId, month, year) {
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
              $currentHrrBoxtitle = $("<div class='box'></div>").appendTo(
                $currentHrrBox
              );
              $("<span>Semana " + currentWeek + "</span>").appendTo(
                $currentHrrBoxtitle
              );
              $(
                "<div class='data-sem'>" +
                  "<p class='porT'></p>" +
                  "<p class='minS'></p>" +
                  "</div>"
              ).appendTo($currentHrrBoxtitle);

              $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
              currentWeek++;
            }

            var $hrrDay = $currentHrrBox.find(".hrr-day");
            var $dayList = $(
              "<ul class='schedule-item' data-date='" +
                entry.calendar_date +
                "'></ul>"
            ).appendTo($hrrDay);
            if (entry.modified === 1) {
              $dayList.css({
                "background-color": "#85929E",
                color: "white",
              });
            }

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
                  const timeSlot = stamp.slice(i, i + 5);
                  const $li = $("<li>" + timeSlot + "</li>");
                  if (
                    stampIndex === 0 &&
                    i === 0 &&
                    (entry.calendar_date === "2024-07-06"
                      ? timeSlot > "10:00"
                      : (userId === 13 && timeSlot > "10:00") ||
                        (userId !== 13 && timeSlot > "09:00"))
                  ) {
                    $li.addClass("late");
                  }

                  if (
                    getMonthWithoutLeadingZero(entry.calendar_date) != month
                  ) {
                    $li.addClass("other");
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
              if (entry.just && entry.just.trim() !== "") {
                $(
                  "<li class='justDoc' data-date='" +
                    entry.calendar_date +
                    "' data-user-id='" +
                    userId +
                    "'><img src='./assets/img/doc.svg' alt=''></li>"
                ).appendTo($dayList);
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
        var adjustmentFactor = 0.5;
        if (year > 2024 || (year === 2024 && month >= 10)) {
          adjustmentFactor = 1;
        }
        var differenceAdjusted = Math.max(0, difference) * adjustmentFactor;
        differenceAdjusted = Math.round(differenceAdjusted);
        var hoursDifference = Math.floor(differenceAdjusted / 60);
        var minutesDifference = differenceAdjusted % 60;

        var differenceAdjustedFormatted =
          (hoursDifference < 10 ? "0" : "") +
          hoursDifference +
          ":" +
          (minutesDifference < 10 ? "0" : "") +
          minutesDifference;

        if (data.total_missing_points > 6) {
          var extraMinutes = (data.total_missing_points - 6) * 15;
          var total_rq_minutes = parseInt(data.total_hours_required) * 60;
          total_rq_minutes += extraMinutes;
          var total_rq_hours = Math.floor(total_rq_minutes / 60);
          var total_rq_remainderMinutes = total_rq_minutes % 60;
          var total_rq =
            total_rq_hours +
            ":" +
            (total_rq_remainderMinutes < 10 ? "0" : "") +
            total_rq_remainderMinutes;
        } else {
          var total_rq = data.total_hours_required + ":00";
        }
        var adjustedHours =
          parseInt(total_rq.split(":")[0]) * 60 +
          parseInt(total_rq.split(":")[1]);

        var sum = adjustedHours + differenceAdjusted;
        var sumHours = Math.floor(sum / 60);
        var sumMinutes = sum % 60;
        var sumFormatted =
          (sumHours < 10 ? "0" : "") +
          sumHours +
          ":" +
          (sumMinutes < 10 ? "0" : "") +
          sumMinutes;
        calcularSumaCalcPorSemana(userId, year, month);

        function timeToMinutes(time) {
          const [hours, minutes] = time.split(":").map(Number);
          return hours * 60 + minutes;
        }
        function calculatePercentage(time1, time2) {
          const minutes1 = timeToMinutes(time1);
          const minutes2 = timeToMinutes(time2);
          return (minutes1 / minutes2) * 100;
        }

        setTimeout(function () {
          $("#porcentHours").html(
            "<b>" +
              calculatePercentage(
                globalTotalMonthlyTimeNuev,
                sumFormatted
              ).toFixed(1) +
              "%"
          );
        }, 500);

        setTimeout(function () {
          const sumFormattedParts = sumFormatted.split(":");
          const sumHours = parseInt(sumFormattedParts[0], 10);
          const sumMinutes = parseInt(sumFormattedParts[1], 10);
          const totalSumFormatted = `${sumHours
            .toString()
            .padStart(2, "0")}:${sumMinutes.toString().padStart(2, "0")}`;
          $("#totalHours").html(
            "<b>" +
              globalTotalMonthlyTimeNuev +
              "h</b><b>" +
              totalSumFormatted +
              "h</b>"
          );
        }, 500);
        $("#totalMissingPoints").text(data.total_missing_points);
        $("#totalLatePoints").text(differenceAdjustedFormatted);
        $("#tarde").text(data.total_late_points);
        $("#tolerancia").html(
          "<b>" +
            data.total_minutes_late_formatted +
            "h</b><b>" +
            data.one_percent_total_hours +
            "h</b>"
        );
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }

  $(document).on("click", ".justDoc", function () {
    var date = $(this).data("date");
    var userId = $(this).data("user-id");

    $.ajax({
      url: "../routes/del/getJust.php",
      method: "POST",
      data: { date: date, userId: userId },
      success: function (response) {
        var data = JSON.parse(response);
        if (data.success) {
          var justFileUrl = data.justFileUrl;
          var $viewDocModal = $(".viewDoc");
          if (justFileUrl.endsWith(".pdf")) {
            $viewDocModal.find("img").hide();
            $viewDocModal.find("embed").attr("src", justFileUrl).show();
          } else {
            $viewDocModal.find("embed").hide();
            $viewDocModal.find("img").attr("src", justFileUrl).show();
          }
          $viewDocModal.show();
          $(document).on("click.hideModal", function (event) {
            var $target = $(event.target);
            if (
              !$target.closest(".viewDoc img").length &&
              !$target.closest(".viewDoc embed").length &&
              !$target.is(".justDoc img")
            ) {
              $viewDocModal.hide();
              $(document).off("click.hideModal");
            }
          });
        } else {
        }
      },
      error: function () {},
    });
  });

  function getUserComments(userId) {
    $.ajax({
      url: "../routes/del/getComments.php",
      method: "POST",
      data: { id_user: userId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          var comments = response.comments;
          var $mensajesDiv = $("#mensajes");
          $mensajesDiv.empty();
          comments.forEach(function (comment) {
            $mensajesDiv.append(
              "<p><strong>Antonio:</strong> " +
                comment.comentario +
                " <span class='fecha'>" +
                comment.created_at +
                "</span></p>"
            );
          });
          $mensajesDiv.show();
        } else {
          $("#mensajes").html("<p>Aun no hay notificaciones.</p>");
        }
      },
      error: function () {
        alert("Error al cargar los comentarios.");
      },
    });
  }

  $(document).ready(function () {
    function getActiveUserId() {
      return $("#userList").find(".active").data("id");
    }

    $("#commentForm").on("submit", function (event) {
      event.preventDefault();
      var userId = getActiveUserId();
      var comentario = $("#commentb").val().trim();

      if (!userId) {
        alert(
          "No se pudo obtener el ID del usuario activo. Intenta nuevamente."
        );
        return;
      }
      if (comentario === "") {
        alert("El comentario no puede estar vacío.");
        return;
      }
      $.ajax({
        url: "../routes/del/insertCommentBoss.php",
        method: "POST",
        data: { user_id: userId, comentario: comentario },
        success: function (response) {
          if (response.success) {
            $("#commentb").val("");
            location.reload();
          } else {
            alert(
              "Hubo un error al guardar el comentario. Intenta nuevamente."
            );
          }
        },
        error: function () {
          alert("Error en la solicitud. Por favor, inténtalo más tarde.");
        },
      });
    });
  });

  updateMonthDisplay();
  if (userList.find(".active").length === 0) {
    userList.find("li").first().addClass("active");
  }
  updateUserDisplay();

  getUserData(selectedUser.attr("data-id"), currentMonth, currentYear);
  getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear);
  getStampSpecial(selectedUser.attr("data-id"), currentMonth, currentYear);
  getLastDayTime(selectedUser.attr("data-id"), currentMonth, currentYear);
  getUserComments(selectedUser.attr("data-id"));
});
