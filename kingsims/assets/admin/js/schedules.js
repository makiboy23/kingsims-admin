$(document).ready(function(){
    $("#clinics").change(function() {
        var clinic_id = $(this).children("option:selected").val();

        if (clinic_id != "") {
            $.get(base_url + "scheduler/get-specialists/" + clinic_id, function( array_list ) {
                $("#specialists").html("<option>Please select</option>");

                if (array_list.length != 0) {
                    $(array_list).each(function(i) {
                        $("#specialists").append("<option value='" + array_list[i].id + "'>" + array_list[i].name + "</option>");
                    });
                }
            });
        }
    });
});