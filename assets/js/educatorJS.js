$(document).ready(function () {

  $(".review-form").submit(function (e) {
    e.preventDefault();

    var formData = $(this).serialize();
    var currentForm = $(this);

    $.ajax({
      type: "POST",
      url: "submit_recommendation.php",
      data: formData,
      success: function (result) {

        if ($.trim(result) == "true") {
          alert("✅ Recommendation submitted successfully");
          currentForm.closest("tr").remove();
        } else {
          alert("❌ Submission failed");
        }
      },
      error: function () {
        alert("❌ Server error");
      }
    });

  });

});
