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

ffunction getUserSchedule(userId) {
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
        var $currentHrrBox;
        var currentDay = null;

        response.schedule.sort(function (a, b) {
          return new Date(a.day_name_espanol) - new Date(b.day_name_espanol);
        });

        daysOfWeek.forEach(function (dayName) {
          var entry = response.schedule.find(function (e) {
            return e.day_name_espanol === dayName;
          });

          if (dayName === "Lunes") {
            if ($currentHrrBox) {
              currentWeek++;
            }
            $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
            $(
              "<span>Semana " + currentWeek + "</span>"
            ).appendTo($currentHrrBox);
            $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
          }

          var $hrrDay = $currentHrrBox.find(".hrr-day");
          var $dayList = $("<ul></ul>").appendTo($hrrDay);

          if (entry) {
            $(
              "<li class='day-nam'>" + dayName.substring(0, 3) + " " + entry.day_number + "</li>"
            ).appendTo($dayList);
            var stamps = entry.stamp.split(",");
            stamps.forEach(function (stamp) {
              for (var i = 0; i < stamp.length; i += 5) {
                $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo($dayList);
              }
            });
          } else {
            $(
              "<li class='day-nam'>" + dayName.substring(0, 3) + " " + (daysOfWeek.indexOf(dayName) + 1) + "</li>"
            ).appendTo($dayList);
            for (var i = 0; i < 4; i++) {
              $("<li></li>").appendTo($dayList);
            }
          }
        });
      } else {
        console.error(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en la solicitud AJAX:", error);
    },
  });
}

  
