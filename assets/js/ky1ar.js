$(document).ready(function () {
  const nextUser = $("#nextUser");
  const previousUser = $("#previousUser");
  const userList = $("#userList");

  const selectedUser = $("#selectedUser");
  const userName = $("#userName");
  const userCategory = $("#userCategory");

  const userImage = $("#userImage");
  const imagePath = "assets/img/profiles/";

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

    selectedUser.attr("data-id", newUser.data("id"));
    userImage.attr("src", imagePath + newUser.data("slug") + ".png");
    userName.text(newUser.data("name"));
    userCategory.text(newUser.data("category"));

    getUserSchedule(newUser.data("id"));
  }

  nextUser.on("click", function () {
    updateUser(1);
  });
  previousUser.on("click", function () {
    updateUser(-1);
  });

  userList.find("li").on("click", function () {
    let $this = $(this);
    userList.find("li").removeClass("active");
    $this.addClass("active");

    selectedUser.attr("data-id", $this.data("id"));
    userImage.attr("src", imagePath + $this.data("slug") + ".png");
    userName.text($this.data("name"));
    userCategory.text($this.data("category"));

    getUserSchedule($this.data("id"));
  });
});

// function getUserSchedule(userId) {
//   $.ajax({
//     url: "../routes/del/get_user_schedule.php",
//     method: "POST",
//     data: { userId: userId },
//     dataType: "json",
//     success: function (response) {
//       if (response.success) {
//         $(".ky1-hrr").empty();

//         var daysCounter = 0;
//         var $currentHrrBox;
//         response.schedule.forEach(function (entry, index) {
//           var dayName = entry.day_name_espanol;
//           var dayNumber = entry.day_number;
//           if (dayName.toLowerCase() === "lunes" || index === 0) {
//             $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
//             $("<span>Semana " + (Math.floor(index / 6) + 1) + "</span>").appendTo($currentHrrBox);
//             $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
//           }

//           var $hrrDay = $currentHrrBox.find('.hrr-day');
//           var $dayList = $("<ul></ul>").appendTo($hrrDay);

//           $("<li class='day-nam'>" + dayName.substring(0, 3) + " " + dayNumber + "</li>").appendTo($dayList);
//           var stamps = entry.stamp.split(",");
//           stamps.forEach(function (stamp) {
//             for (var i = 0; i < stamp.length; i += 5) {
//               $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo($dayList);
//             }
//           });
//           if (dayName.toLowerCase() === "sábado" || index === response.schedule.length - 1) {
//             $currentHrrBox = null;
//           }

//           daysCounter++;
//         });
//         console.log(response.schedule);
//       } else {
//         console.error(response.message);
//       }
//     },

//     error: function (xhr, status, error) {
//       console.error("Error en la solicitud AJAX:", error);
//     },
//   });
// }

function getUserSchedule(userId) {
  $.ajax({
    url: "../routes/del/get_user_schedule.php",
    method: "POST",
    data: { userId: userId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        $(".ky1-hrr").empty();

        var daysOfWeek = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        var currentWeek = 1;
        var $currentHrrBox = null;
        var currentDay = null;

        response.schedule.sort(function (a, b) {
          return new Date(a.day_name_espanol) - new Date(b.day_name_espanol);
        });

        response.schedule.forEach(function (entry, index) {
          var dayName = entry.day_name_espanol;
          var dayNumber = entry.day_number;

          if (dayName.toLowerCase() === "lunes" || index === 0) {
            $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
            $("<span>Semana " + currentWeek + "</span>").appendTo($currentHrrBox);
            $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
          }

          var $hrrDay = $currentHrrBox.find(".hrr-day");
          if ($hrrDay.length === 0) {
            $hrrDay = $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
          }

          var $dayList = $("<ul></ul>").appendTo($hrrDay);

          $("<li class='day-nam'>" + dayName.substring(0, 3) + " " + dayNumber + "</li>").appendTo($dayList);
          var stamps = entry.stamp.split(",");
          stamps.forEach(function (stamp) {
            for (var i = 0; i < stamp.length; i += 5) {
              $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo($dayList);
            }
          });

          if (dayName.toLowerCase() === "sábado" || index === response.schedule.length - 1) {
            $currentHrrBox = null;
            currentWeek++;
          }
        });

        // Agregar días faltantes
        var lastDayName = response.schedule[response.schedule.length - 1].day_name_espanol;
        var lastDayNumber = response.schedule[response.schedule.length - 1].day_number;
        var lastDayIndex = daysOfWeek.indexOf(lastDayName);

        for (var i = lastDayIndex + 1; i < daysOfWeek.length; i++) {
          var dayName = daysOfWeek[i];
          var dayNumber = lastDayNumber + (i - lastDayIndex);

          if ($currentHrrBox === null) {
            $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
            $("<span>Semana " + currentWeek + "</span>").appendTo($currentHrrBox);
          }

          var $hrrDay = $currentHrrBox.find(".hrr-day");
          if ($hrrDay.length === 0) {
            $hrrDay = $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
          }

          var $dayList = $("<ul></ul>").appendTo($hrrDay);

          $("<li class='day-nam'>" + dayName.substring(0, 3) + " " + dayNumber + "</li>").appendTo($dayList);
          for (var j = 0; j < 4; j++) {
            $("<li></li>").appendTo($dayList);
          }
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
