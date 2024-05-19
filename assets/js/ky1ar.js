// $(document).ready(function () {
//   const nextUser = $("#nextUser");
//   const previousUser = $("#previousUser");
//   const userList = $("#userList");
//   const nextMonth = $("#nextMonth");
//   const previousMonth = $("#previousMonth");

//   const selectedUser = $("#selectedUser");
//   const userName = $("#userName");
//   const userCategory = $("#userCategory");

//   const userImage = $("#userImage");
//   const imagePath = "assets/img/profiles/";

//   const monthNames = [
//     "Enero",
//     "Febrero",
//     "Marzo",
//     "Abril",
//     "Mayo",
//     "Junio",
//     "Julio",
//     "Agosto",
//     "Septiembre",
//     "Octubre",
//     "Noviembre",
//     "Diciembre",
//   ];
//   let currentMonth = new Date().getMonth() + 1; // Mes actual (1 a 12)
//   let currentYear = new Date().getFullYear(); // Año actual

//   function updateMonthDisplay() {
//     $(".ky1-dte span").text(`${monthNames[currentMonth - 1]}, ${currentYear}`);
//   }

//   function updateUserDisplay() {
//     const activeUser = userList.find(".active");
//     selectedUser.attr("data-id", activeUser.data("id"));
//     userImage.attr("src", imagePath + activeUser.data("slug") + ".png");
//     userName.text(activeUser.data("name"));
//     userCategory.text(activeUser.data("category"));
//   }

//   function updateUser(offset) {
//     let current = userList.find(".active").index();
//     let total = userList.find("li").length - 1;
//     userList.find("li").removeClass("active");

//     current = current + offset;
//     if (offset == 1) {
//       if (current > total) current = 0;
//     } else if (offset == -1) {
//       if (current < 0) current = total;
//     }

//     let newUser = userList.find("li").eq(current);
//     newUser.addClass("active");
//     updateUserDisplay();

//     getUserSchedule(newUser.data("id"), currentMonth, currentYear); // Pasa mes y año actual
//   }

//   nextUser.on("click", function () {
//     updateUser(1);
//   });
//   previousUser.on("click", function () {
//     updateUser(-1);
//   });

//   nextMonth.on("click", function () {
//     currentMonth = (currentMonth % 12) + 1;
//     if (currentMonth === 1) currentYear++;
//     updateMonthDisplay();
//     getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); // Pasa mes y año actual
//   });

//   previousMonth.on("click", function () {
//     currentMonth = currentMonth === 1 ? 12 : currentMonth - 1;
//     if (currentMonth === 12) currentYear--;
//     updateMonthDisplay();
//     getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); // Pasa mes y año actual
//   });

//   userList.find("li").on("click", function () {
//     userList.find("li").removeClass("active");
//     $(this).addClass("active");
//     updateUserDisplay();

//     getUserSchedule($(this).data("id"), currentMonth, currentYear); // Pasa mes y año actual
//   });

//   function getUserSchedule(userId, month, year) {
//     console.log(
//       `Fetching schedule for userId: ${userId}, month: ${month}, year: ${year}`
//     ); // Depuración
//     $.ajax({
//       url: "../routes/del/get_user_schedule.php",
//       method: "POST",
//       data: { userId: userId, month: month, year: year },
//       dataType: "json",
//       success: function (response) {
//         if (response.success) {
//           $(".ky1-hrr").empty();
//           var daysCounter = 0;
//           var $currentHrrBox;
//           var currentWeek = 1;
//           response.schedule.forEach(function (entry, index) {
//             var dayName = entry.day_name_espanol;
//             var dayNumber = entry.day_number;

//             // Omitir los domingos
//             if (dayName.toLowerCase() === "domingo") {
//               return; // Salta este día y continúa con el siguiente
//             }

//             if (dayName.toLowerCase() === "lunes" || index === 0) {
//               $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(
//                 ".ky1-hrr"
//               );
//               $("<span>Semana " + currentWeek + "</span>").appendTo(
//                 $currentHrrBox
//               );
//               $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
//               currentWeek++; // Aumenta el contador de semana
//             }

//             var $hrrDay = $currentHrrBox.find(".hrr-day");
//             var $dayList = $("<ul></ul>").appendTo($hrrDay);

//             $(
//               "<li class='day-nam'>" +
//                 dayName.substring(0, 3) +
//                 " " +
//                 dayNumber +
//                 "</li>"
//             ).appendTo($dayList);

//             if (entry.holiday == 1) {
//               // Si es un feriado, muestra "FERIADO" en una sola línea
//               $("<li class='test'>FERIADO</li>").appendTo($dayList);
//             } else if (entry.stamp) {
//               // Verifica si hay datos de estampas
//               var stamps = entry.stamp.split(",");
//               stamps.forEach(function (stamp) {
//                 for (var i = 0; i < stamp.length; i += 5) {
//                   $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo(
//                     $dayList
//                   );
//                 }
//               });
//             } else {
//               // Si no hay estampas, muestra un elemento vacío
//               $("<li></li>").appendTo($dayList);
//             }

//             daysCounter++;
//           });

//           console.log(response.schedule); // Verifica los datos recibidos
//         } else {
//           console.error(response.message);
//         }
//       },
//       error: function (xhr, status, error) {
//         console.error("Error en la solicitud AJAX:", error);
//       },
//     });
//   }

//   // Inicializa el mes y el usuario al cargar la página v2
//   updateMonthDisplay();
//   if (userList.find(".active").length === 0) {
//     userList.find("li").first().addClass("active");
//   }
//   updateUserDisplay();
//   getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); // Cargar horario del usuario activo al inicio
// });
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

    getUserSchedule(newUser.data("id"), currentMonth, currentYear); // Pasa mes y año actual
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
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); // Pasa mes y año actual
  });

  previousMonth.on("click", function () {
    currentMonth = currentMonth === 1 ? 12 : currentMonth - 1;
    if (currentMonth === 12) currentYear--;
    updateMonthDisplay();
    getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); // Pasa mes y año actual
  });

  userList.find("li").on("click", function () {
    userList.find("li").removeClass("active");
    $(this).addClass("active");
    updateUserDisplay();

    getUserSchedule($(this).data("id"), currentMonth, currentYear); // Pasa mes y año actual
  });

  function getUserSchedule(userId, month, year) {
    console.log(
      `Fetching schedule for userId: ${userId}, month: ${month}, year: ${year}`
    ); // Depuración
    var diff_time;
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
            var dayName = entry.day_name_espanol;
            var dayNumber = entry.day_number;

            // Omitir los domingos
            if (dayName.toLowerCase() === "domingo") {
              return; // Salta este día y continúa con el siguiente
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
              // Si no hay estampas, muestra un elemento vacío
              $("<li></li>").appendTo($dayList);
            }

            const listItem = $(this);
            const userId = selectedUser.attr("data-id");
            const calendarDate = listItem.data("date");
            $.ajax({
              url: "../routes/del/get_time_difference.php",
              method: "POST",
              data: { userId: userId, calendarDate: calendarDate },
              dataType: "json",
              success: function (response) {
                console.log(response);
              },
              error: function (xhr, status, error) {
                console.error("Error en la solicitud AJAX:", error);
              },
            });
            // Añadir el elemento calc con los datos necesarios
            // $("<li class='calc' data-date='" + entry.calendar_date + "'></li>").appendTo($dayList);

            daysCounter++;
          });

          // Aquí puedes hacer la llamada AJAX para cada .calc
          // $(".calc").each(function () {
          //   const listItem = $(this);
          //   const userId = selectedUser.attr("data-id");
          //   const calendarDate = listItem.data("date"); // Asegúrate de que cada .calc tenga un data-date

          //   console.log(`Solicitando diferencia de tiempo para userId: ${userId}, calendarDate: ${calendarDate}`); // Debug
          // });

        } else {
          console.error(response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }

  // Inicializa el mes y el usuario al cargar la página
  updateMonthDisplay();
  if (userList.find(".active").length === 0) {
    userList.find("li").first().addClass("active");
  }
  updateUserDisplay();
  getUserSchedule(selectedUser.attr("data-id"), currentMonth, currentYear); // Cargar horario del usuario activo al inicio
});
