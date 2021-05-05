(function(hex, undefined) {

    var elem = document.getElementById("ww-mapa"),
            area = [],
            clickedFields = {},
            regions = {},
            markerType = 0,
            pointerOn = false;

    // Creating a grid
    var grid = hex.grid(elem, {startX: 93, startY: -58});

    // Element to show the currently hovered tile
    var curr = document.createElement("div");
    curr.className = curr.className + " tile_active";
    curr.style.display = "none";
    var curratt = document.createAttribute("id");
    curratt.value = "cursor";
    curr.setAttributeNode(curratt);
    grid.root.appendChild(curr);

    function setMarkerType(type)
    {
        markerType = type;
        pointerOn = false;
        $("#fieldsTypes li").each(function(index) {
            if (parseInt($(this).attr('value')) == markerType)
            {
                $('#activeElement').html($(this).html()).attr("fieldType", markerType);
            }
        });
    }

    function getArea() {
        // y 113 x 132

        var arrtapcos = {};
        var x = 0;
        var y = 0;
        for (i = 0; i < 16000; i++) {
            var key = hex.key(x, y);
            arrtapcos[key] = {x: x, y: y};

            if (x === 132 && y === -179)
                break;

            if (y % (-113 + Math.ceil(x / 2) * -1) === 0 && y !== 0)
            {
                x++;
                y = Math.ceil(x / 2) * -1;
            }
            else
                y--;
        }

        return arrtapcos;
    }

    function visitNeighbors(x, y, callback) {
        var i, j;
        for (i = -1; i < 2; i++) {
            for (j = -1; j < 2; j++) {
                if (i !== j) {
                    callback(x + i, y + j);
                }
            }
        }
    }

    function addRegion(x, y, fieldType)
    {
        var key = hex.key(x, y);
        if (typeof area[key] === 'undefined')
            return;
        if (typeof regions[key] !== 'undefined' && parseInt(regions[key].fieldType) === parseInt(fieldType))
            return;

        if (typeof regions[key] !== 'undefined')
        {
            var id = 'region:' + x + ':' + y;
            $('div.map-assets div').each(function() {
                if ($(this).attr('id') == id)
                {
                    $(this).removeClass("tile_podloze" + regions[key].fieldType).addClass("tile_podloze" + fieldType);
                }
            });
        }
        else
        {
            inv = grid.screenpos(x, y);
            $('div.map-assets').append('<div class="tile_active tile_podloze' + fieldType + '" style="left: ' + inv.x + 'px; top: ' + inv.y + 'px;" id="region:' + x + ':' + y + '"></div>');
        }

        regions[key] = {x: x, y: y, fieldType: fieldType};
    }

    area = getArea();

    grid.addEvent("tileover", function(e, x, y) {
        var inv = grid.screenpos(x, y);
        curr.style.left = inv.x + "px";
        curr.style.top = inv.y + "px";
        if (typeof area[hex.key(x, y)] != 'undefined') {
            curr.innerHTML = [x, y] + '';
        }
    }, 'cursor');

    // region to match area
    region = hex.reg = hex.region(grid, {
        inside: function(x, y) {
            return (hex.key(x, y) in area);
        }
    });
    region.addEvent("regionout", function(e, x, y) {
        curr.style.display = "none";
    });
    // Setting mouse movement related tile events
    region.addEvent("regionover", function(e, x, y) {
        curr.style.display = "";
    });

    region.addEvent("regionclick", function(e, x, y) {
        if (markerType == 0)
            return;

        if (pointerOn == true) {
            grid.removeEvent('tileover', 'markTiles');
            pointerOn = false;
        }
        else
        {
            grid.addEvent('tileover', function(e, x, y) {
                addRegion(x, y, markerType);
            }, 'markTiles');
            addRegion(x, y, markerType);
            pointerOn = true;
        }
    }, 'click');

    $.ajax({
        url: 'https://www.wichry-wojny.eu/dane-mapyE',
        type: 'GET',
        dataType: 'json',
        beforeSend: function(xhr) {
            $('#messages').html('Ładuję dane mapy').show();
        },
        success: function(data) {
            $.each(data.map, function(index, value) {
                addRegion(value.x, value.y, value.fieldType);
            });
        },
        error: function() {
            $('#messages').html('').hide();
            alert('Nie udało się pobrać danych');
        }
    }).done(function(data) {
        $('#messages').html('').hide();
    });

    $("#fieldsTypes li").click(function() {
        pointerOn = false;
        markerType = $(this).attr("value");
        $('#activeElement').html($(this).html()).attr("fieldType", markerType);
    });
    $('#sendData').click(function() {

        var jsonData = {};
        $.each(regions, function(index, value) {
            jsonData[index] = value.fieldType;
        });
        $.ajax({
            url: 'https://www.wichry-wojny.eu/zapisz-dane-mapy',
            type: 'POST',
            data: {mapData: jsonData},
            dataType: 'json',
            beforeSend: function(xhr) {
                $('#messages').html('Zapisuje dane mapy').show();
            },
            success: function(data) {
                $('#messages').html('').hide();
            },
            error: function() {
                $('#messages').html('').hide();
                alert('Nie udało się zapisać danych');
            }
        }).done(function(data) {
            $('#messages').html('').hide();
            setMarkerType(0);
        });

    });

})(window.hex);