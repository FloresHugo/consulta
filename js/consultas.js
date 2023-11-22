let markers = [];
let marker;
let map
let canShowBtn = true;
let selected = false;

function setDate() {
    let date = new Date();
    let hours = date.getHours();


    switch (loadTime) {
        case 1:
            $('#c-1').prop('checked', true);
            break;
        case 6:
            $('#c-6').prop('checked', true);
            break;
        case 8:
            $('#c-8').prop('checked', true);
            break;
        case 11:
            $('#c-11').prop('checked', true);
            break;
        case 15:
            $('#c-15').prop('checked', true);
            break;
        case 19:
            $('#c-19').prop('checked', true);
            break;
    }

    let startDate = $('#startDate').val();
    let endtDate = $('#endDate').val();
    let currentMonth = (date.getUTCMonth() + 1) < 10 ? `0${date.getUTCMonth() + 1}` : date.getUTCMonth() + 1;
    let currentDay = date.getDate() < 10 ? `0${date.getDate()}` : date.getDate();
    let currentDate = `${date.getFullYear()}-${currentMonth}-${currentDay}`;

    if (startDate == currentDate) {
        if (hours > loadTime && loadTime <= 1)
            $('#c-1').prop('disabled', true);
        if (hours > loadTime && loadTime <= 6)
            $('#c-6').prop('disabled', true);
        if (hours > loadTime && loadTime <= 8)
            $('#c-8').prop('disabled', true);
        if (hours > loadTime && loadTime <= 11)
            $('#c-11').prop('disabled', true);
        if (hours > loadTime && loadTime <= 15)
            $('#c-15').prop('disabled', true);
        if (hours > loadTime || loadTime <= 19)
            $('#c-19').prop('disabled', true);

    } else {
        $('.loads').prop('disabled', false);
    }
    switch (loadTime) {
        case 1:
            $('#c-1').prop('checked', true);
            $('#c-1').prop('disabled', false);
            break;
        case 6:
            $('#c-6').prop('checked', true);
            $('#c-6').prop('disabled', false);
            break;
        case 8:
            $('#c-8').prop('checked', true);
            $('#c-8').prop('disabled', false);
            break;
        case 11:
            $('#c-11').prop('checked', true);
            $('#c-11').prop('disabled', false);
            break;
        case 15:
            $('#c-15').prop('checked', true);
            $('#c-15').prop('disabled', false);
            break;
        case 19:
            $('#c-19').prop('checked', true);
            $('#c-19').prop('disabled', false);
            break;
    }
}


$('.option').change(function () {
    if (canShowBtn) $('#search-btn').show();
});
$('.date').change(function () {
    setDate();
});

$('.search-btn').click(function () {
    setMapOnAll(null);
    markers = [];

    $('#loading').modal('show')
    clearTable();
    main(true);
    $('#search-btn').show();
});

$(".download").click(function () {
    let table = document.getElementById("table-data");
    TableToExcel.convert(table, { 
        name: `SCPI- consulta.xlsx`, 
        sheet: {
            name: 'Sheet 1' 
        }
    });
});

$("#download_all").click(function () {
    $('#loading').modal('show')
    $('.th-add-all').remove();
    $('#data-all').html('');
    getAllData();
    
});

$(".date").change(function () {
    validateDate();
});

$("body").on('click','.selectRow',function(){
    let checked = $(this).is(':checked');
    let com = $(this).data('com');
    let clenaCom = com.replace(/\//g, '');

    updateCheck(com,checked);

    $(".selectRow").attr('disabled',true);
    $(this).addClass('d-none');
    $(`#loading_${clenaCom}`).removeClass('d-none');
});

$("body").on('change','.selectRow', function(){
    $(".download-btn").addClass('d-none');
    $("#update_table").removeClass('d-none');
    $("#delete_selected").removeClass('d-none');

    if ($('.selectRow:checked').length == 0 && !selected) {
        $(".download-btn").removeClass('d-none');
        $("#update_table").addClass('d-none');
        $("#delete_selected").addClass('d-none');
    }
});

$("#delete_selected").click(function(){
    let data = {
        current: originPermiso
    }
    
    $.ajax({
        method: "DELETE",
        url: `/actions/data`,
        data: data,
        dataType: "JSON",
        statusCode: {
            200: function () {
                if (selected){
                    $('#loading').modal('show')
                    clearTable();
                    main(true);
                    $('#search-btn').show();
                }else{
                    $(".selectRow").attr('checked', false)
                }
                
            }
        }
    });
});

$(document).on("ajaxComplete", function () {
    $('#loading').modal('show')
});
// Initialize and add the map
async function main(reload = false) {
    // The location of Uluru
    const uluru = { lat: lat, lng: lng };
    // The map, centered at Uluru
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 9,
        center: uluru,
    });



    new google.maps.Circle({
        strokeColor: "#04c3fd",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#04c3fd",
        fillOpacity: 0.35,
        map,
        center: uluru,
        radius: 40 * 1000,
    });

    getData(map, reload);
}

function setMapOnAll(map) {
    for (let i = 0; i < markers.length; i++) {
        markers[i].setMap(map);
    }
}

function AddMarker(map, locations) {

    let permiso;
    const url = 'https://petrointelligence.com/sistema/indices/';
    let j = 0;
    for (const i of locations) {
        permiso = i.permiso;
        let category = ({
            'C': '1.png',
            'DT': '2.png',
            'DP': '3.png',
            'OK': '4.png',
            'CLOSED': 'cerrado.png',
        })[i.category];
        let pre = '';
        let services = '';
        if (i.long)
            pre = `<img src="${url}../relojpi.png" style="width:15px;">`;
        if (i.min)
            pre = `<img src="${url}../verde_t.png" style="width:15px;">`;
        if (i.max)
            pre = `<img src="${url}../rojo_t.png" style="width:15px;">`;

        if (i.profeco)
            services += `<img src="${url}litros.png" style="width:9px;">`;
        if (i.vales)
            services += `<img src="${url}tarjeta.png" style="width:15px;">`;
        if (i.wc)
            services += `<img src="${url}wc.png" style="width:15px;">`;
        if (i.food)
            services += `<img src="${url}restaurante.png" style="width:15px;">`;
        if (i.shop)
            services += `<img src="${url}tienda.png" style="width:15px;">`;
        
        current = '';
        if (i.isCurrent) {
            current = '<i class="fas fa-location-arrow" style="color: #00b34d;"></i>';
        }

        let content = `
            <a style="font-size:12px;"> ${current} ${permiso}</a>
            <br>
            ${pre}
            <a style="font-size:15px;">${i.price}</a>
            <a style="width:15px;" href="https://petrointelligence.com/sistema/indices/gasolinera.php?permiso=${permiso}" target="_blank"> Más...</a>
            <br>
            <img src="${url}${category}" style="width:15px;"> 
            ${services}
        `;
        current = '';
        
        let infowindow = new google.maps.InfoWindow({
            content: content,
            ariaLabel: permiso,
        });
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(i.lat, i.lng),
            title: permiso,
            icon: `${url}${i.icon}`,
            map: map
        });
        markers.push(marker);
        google.maps.event.addListener(marker, 'click', (function (marker) {
            return function () {
                infowindow.open(map, marker);
            }
        })(marker, i));

        infowindow.open(map, marker);
    }
}

function getData(map = false, reload = true) {
    let startDate = $('#startDate').val();
    let endDate = $('#endDate').val();
    let fuel = $('#fuel').val();
    let time = $('input[name=corte]:checked').val();

    let data = {
        lat: lat,
        lng: lng,
        permiso: permiso,
        startDate: startDate,
        endDate: endDate,
        fuel: fuel,
        time: time,
        reload: reload,
        current: originPermiso
    }
    $.ajax({
        method: "GET",
        url: `/actions/data`,
        data: data,
        dataType: "JSON",
        success: function (data) {
            $('#loading').modal('hide');
            fillTable(data.data.history);
            addHeaders(data.data.days);
            makeMin(data.data.maxMin);
            makeMax(data.data.maxMin);
            makeAverageRow(data.data.averages);
            makeCards(data.data.cards);
            if (!reload) {setFuels(data.data.fuels);}
            $('#search-btn').hide();
            $('#loading').modal('hide');
            selected = data.data.selected;
            if (selected) {
                $(".selectRow").attr('checked',true)
                $(".download-btn").removeClass('d-none');
                $("#update_table").addClass('d-none');
                $("#delete_selected").removeClass('d-none');
            }
            AddMarker(map, data.data.locations);
            $('#loading').modal('hide');

        },
        error: function (e) {
            $('#loading').modal('hide')
            alertaError("Error inesperado, vuelve a interlo");
        },
    });
    google.maps.event.addListenerOnce(map, 'tilesloaded', function () {
        // El mapa ha terminado de cargar
        console.log('El mapa ha terminado de cargar');
        $('#loading').modal('hide');
        // Puedes realizar acciones adicionales una vez que el mapa ha cargado completamente
    });
}

function getAllData() {
    let startDate = $('#startDate').val();
    let endDate = $('#endDate').val();
    let time = $('input[name=corte]:checked').val();

    let data = {
        lat: lat,
        lng: lng,
        permiso: permiso,
        startDate: startDate,
        endDate: endDate,
        time: time,
        current: originPermiso
    }
    $.ajax({
        method: "GET",
        url: `/actions/download_all`,
        data: data,
        dataType: "JSON",
        success: function (data) {
            console.log(data);     
            fillTableAll(data.data.history,data.data.fuels,data.data.days);
            addHeadersAll(data.data.days, data.data.total_fuels);
            makeTypeFuelHeader(data.data.fuels, data.data.days.length);
            let table = document.getElementById("table-data-all");
            TableToExcel.convert(table, {
                name: `SCPI - consulta - todos los productos.xlsx`,
                sheet: {
                    name: 'Sheet 1'
                }
            });
            $('#loading').modal('hide');

        },
        error: function (e) {
            $('#loading').modal('hide')
            alertaError("Error inesperado, vuelve a interlo");
        },
    });
}

function setFuels(fuels) {
    $('#fuel').html("");
    for(let i = 0; i < fuels.keys.length; i++){
        $('#fuel').append(`<option value="${fuels.keys[i]}">${fuels.products[i]}</option>`);
    }
}

function fillTable(h) {
    let history = Object.entries(h)
    history = history.sort(((a, b) => a[1].distance - b[1].distance));

    const obj = {};

    for (let i = 0; i < history.length; i++) {
        const key = history[i][0];
        const value = history[i][1];
        obj[key] = value;
    }

    history = obj;
    let first = true;

    for (const i in history) {
        makeRow(history[i].permiso, history[i].name, history[i].brand, parseFloat(history[i].distance).toFixed(2), history[i].days, history[i].updated_at, first)
        first = false;
    }
}

function fillTableAll(h,fuels,days) {
    let history = Object.entries(h)
    history = history.sort(((a, b) => a[1].distance - b[1].distance));

    const obj = {};

    for (let i = 0; i < history.length; i++) {
        const key = history[i][0];
        const value = history[i][1];
        obj[key] = value;
    }

    history = obj;
    let first = true;

    for (const i in history) {
        makeRowAll(history[i].permiso, history[i].name, history[i].brand, parseFloat(history[i].distance).toFixed(2), history[i].fuels, history[i].updated_at, first, fuels, days)
        first = false;
    }
}

function makeRow(com, name, brand, distance, price, updated_at, first) {
    let prices = makePrice(price, null, 0, first);
    let style = first ? `class="here" data-fill-color="dff0d8"` : '';
    let cellStyle = first ? `data-fill-color="dff0d8"` : '';
    let ClenaCom = com.replace(/\//g, '');
    let check = ` <div class="form-check">
                    <input class="form-check-input selectRow" id="check_${ClenaCom}" type="checkbox" value="" data-com="${com}">
                    <div class="spinner-grow text-secondary d-none" id="loading_${ClenaCom}" role="status" style="width:0.7rem;height:0.7rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>`;
    let haveCheck = first ? '' : check;
    let icon = '<svg class="svg-inline--fa fa-external-link-alt fa-w-16" style="height: 10px;" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"></path></svg>';
    const row = `
        <tr ${style}>
            <td data-exclude="true" ${cellStyle}>
                ${haveCheck}
            </td>
            <td ${cellStyle}><a href="https://petrointelligence.com/sistema/indices/gasolinera.php?permiso=${com}" target="_blanck" class="text-dark">${com}<sup>${icon}<sup></a></td>
            <td ${cellStyle}>${name}</td>
            <td ${cellStyle}>${brand}</td>
            <td ${cellStyle}>${distance}Km.</td>
            ${prices}
            <td>${updated_at}</td>
        </tr>
    `;
    $('#data').append(row);
}
function makeRowAll(com, name, brand, distance, price, updated_at, first,fuels,days) {
    let prices = getFuelTypeData(price,days, fuels, first);
    let style = first ? `class="here" data-fill-color="dff0d8"` : '';
    let cellStyle = first ? `data-fill-color="dff0d8"` : '';
    let ClenaCom = com.replace(/\//g, '');
    let icon = '<svg class="svg-inline--fa fa-external-link-alt fa-w-16" style="height: 10px;" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"></path></svg>';
    const row = `
        <tr ${style}>
            <td ${cellStyle}><a href="https://petrointelligence.com/sistema/indices/gasolinera.php?permiso=${com}" target="_blanck" class="text-dark">${com}<sup>${icon}<sup></a></td>
            <td ${cellStyle}>${name}</td>
            <td ${cellStyle}>${brand}</td>
            <td ${cellStyle}>${distance}Km.</td>
            ${prices}
        </tr>
    `;
    $('#data-all').append(row);
}


function addHeaders(headers) {
    for (const i of headers) {
        makeHeader(i);
    }
    $('#data-headers').append(`<th class="th-add" data-fill-color="e6e6e6" data-f-bold="true">Última actualización</th>`);
}

function addHeadersAll(headers,totalFuels) {
    for (let i = 0; i < totalFuels; i++) {
        for (const i of headers) {
            makeHeaderAll(i);
        }
        $('#data-headers-all').append(`<th class="th-add-all" data-fill-color="e6e6e6" data-f-bold="true">Último cambio de precio</th>`);
    }
    
}

function makeExtras() {

}

function makeHeader(data) {
    $('#data-headers').append(`<th class="th-add" data-fill-color="e6e6e6" data-f-bold="true">${data}</th>`);
}

function makeHeaderAll(data) {
    $('#data-headers-all').append(`<th class="th-add-all" data-fill-color="e6e6e6" data-f-bold="true">${data}</th>`);
}

function getFuelTypeData(pricesList, days, fuel, first = 0){
    let prices;
    for (const i in fuel) {
        if (fuel[i] in pricesList){
            prices += makePrice(pricesList[fuel[i]].days, pricesList[fuel[i]].updated_at, 1, first);
        }else{
            let mock = makeMockPrice(days);
            prices += makePrice(mock, '--', 1, first);
        }
        
    }

    return prices;
}

function makeMockPrice(days){
    let mock = {};
    for (let i = 0; i < days.length; i++) {
        mock[days[i]] = {
            "day": days[i],
            "price": "--",
            "at": 0,
            "longSt": 0        
        }
    }

    return mock;
}

function makePrice(prices, date, isAll = false, first = 0) {
    let price = '';
    let style = '';
    let cellStyle = first ? `data-fill-color="dff0d8"` : '';
    let up = '<svg xmlns="http://www.w3.org/2000/svg" width="1080" height="1080" viewBox="0 0 1080 1080" xml:space="preserve" style="height: 1rem;width: 1rem;"><path style="stroke:none;stroke-width:1;stroke-dasharray:none;stroke-linecap:butt;stroke-dashoffset:0;stroke-linejoin:miter;stroke-miterlimit:4;fill:#48a434;fill-rule:nonzero;opacity:1" transform="translate(162 -2.025) scale(33.75)" d="M7.844 26.844h6.719c.813 0 1.406-.625 1.406-1.438v-7.813h5.313c.5 0 .875-.25 1.063-.719.063-.156.063-.25.063-.375 0-.313-.094-.563-.313-.781L11.97 5.624c-.344-.469-1.125-.438-1.563 0L.313 15.718c-.688.625-.219 1.875.813 1.875H6.47v7.813c0 .813.563 1.438 1.375 1.438z"/></svg>';
    let down = '<svg xmlns="http://www.w3.org/2000/svg" width="1080" height="1080" viewBox="0 0 1080 1080" xml:space="preserve" style="height: 1rem;width: 1rem;"><path style="stroke:none;stroke-width:1;stroke-dasharray:none;stroke-linecap:butt;stroke-dashoffset:0;stroke-linejoin:miter;stroke-miterlimit:4;fill:#b32b2b;fill-rule:nonzero;opacity:1" transform="rotate(180 459 541.013) scale(33.75)" d="M7.844 26.844h6.719c.813 0 1.406-.625 1.406-1.438v-7.813h5.313c.5 0 .875-.25 1.063-.719.063-.156.063-.25.063-.375 0-.313-.094-.563-.313-.781L11.97 5.624c-.344-.469-1.125-.438-1.563 0L.313 15.718c-.688.625-.219 1.875.813 1.875H6.47v7.813c0 .813.563 1.438 1.375 1.438z"/></svg>';
    let arrow = '';

    for (const i in prices) {
        if (prices[i].at){
            style = 'atypical';
            cellStyle = `data-fill-color="f9f2ec"`;
        }
        if (prices[i].longSt){
            style = 'longSt';
            cellStyle = `data-fill-color="fff0e6"`;
        }
        if (prices[i].at && prices[i].longSt){
            style = 'at-lon';
            cellStyle = `data-fill-color="f9ffe6"`;
        }
        if (prices[i].diff == 'up') {
            arrow = up;
        }
        if (prices[i].diff == 'down') {
            arrow = down;
        }

        price += `<td class="${style} " ${cellStyle}><span class="d-flex">${arrow} ${prices[i].price}</span></td>`;
        style = '';
        arrow = '';
    }
    if(isAll){
        price += `<td class="${style} " ${cellStyle}><span class="d-flex">${date}</span></td>`;
    }

    return price;
}

function makePriceAll(prices, first = 0) {
    let price = '';
    let style = '';
    let cellStyle = first ? `data-fill-color="dff0d8"` : '';
    for (const i in prices) {
        if (prices[i].at) {
            style = 'atypical';
            cellStyle = `data-fill-color="f9f2ec"`;
        }
        if (prices[i].longSt) {
            style = 'longSt';
            cellStyle = `data-fill-color="fff0e6"`;
        }
        if (prices[i].at && prices[i].longSt) {
            style = 'at-lon';
            cellStyle = `data-fill-color="f9ffe6"`;
        }

        price += `<td class="${style}" ${cellStyle}>${prices[i].price}</td>`;
        style = '';
    }

    return price;
}

function clearTable() {
    $('.th-add').remove();
    $('.th-add-all').remove();
    $('#data').html('');
    $('#data-all').html('');
}

function makeTypeFuelHeader(fuels, days){
    for (let i = 0; i < fuels.length; i++) {
        switch (fuels[i]) {
            case 1:
                $('#type_fuel').append(`<td colspan="${days + 1}" class="th-add-all text-center bg-success text-light" data-f-color="f8f9fa" data-fill-color="28a745" data-f-bold="true">Regular</td>`);
                break;
            case 2:
                $('#type_fuel').append(`<td colspan="${days + 1}" class="th-add-all text-center bg-danger text-light" data-f-color="f8f9fa" data-fill-color="dc3545" data-f-bold="true">Premium</td>`);
                break;
            case 3:
                $('#type_fuel').append(`<td colspan="${days + 1}" class="th-add-all text-center bg-dark  text-light" data-f-color="f8f9fa" data-fill-color="343a40" data-f-bold="true">Diesel</td>`);
                break;
            default:
                break;
        }        
    }
}

function makeAverageRow(averages) {
    let row = `
        <tr>
            <td class="text-center" colspan="4">Promedio</td>
    `;
    for (const i in averages) {
        row += `<td>${averages[i]}</td>`;
    }
    row += `</tr>`;

    $('#data').append(row);
}

function makeMax(data) {
    let row = `
        <tr>
            <td class="text-center" colspan="4">Máximo</td>
    `;
    for (const i in data) {
        row += `<td>${data[i].max}</td>`;
    }

    row += `</tr>`;
    $('#data').append(row);
}

function makeMin(data) {
    let row = `
        <tr>
            <td class="text-center" colspan="4">Mínimo</td>
    `;
    for (const i in data) {
        row += `<td>${data[i].min}</td>`;
    }

    row += `</tr>`;
    $('#data').append(row);
}

function makeCards(cards) {
    $('#others').text(cards.stations);
    $('#distance').text(cards.distance);
    $('#price').text(cards.average);
    $('#products').text(cards.products);
    $('#average').text(cards.days);
}

function validateDate(){
    let startDate = $('#startDate').val() + '06:00:00';
    let endDate = $('#endDate').val() + '06:00:00';

    strarDate = new Date()
    
    if(startDate > endDate){
        $('#startDate').addClass('is-invalid');
        $('#endDate').addClass('is-invalid');
        $('#search-btn').hide();
        canShowBtn = false;
        return;
    }
    canShowBtn = true; 
    $('#search-btn').show();
    $('#startDate').removeClass('is-invalid');
    $('#endDate').removeClass('is-invalid');
}

function updateCheck(com,type){
    let data = {
        com: com,
        current: originPermiso,
        type: type
    }
    let clenaCom = com.replace(/\//g, '');

    $.ajax({
        method: "POST",
        url: `/actions/update_comp`,
        data: data,
        dataType: "JSON",
        success: function (data) {
            if(data.status != 'OK'){
                $(".selectRow").attr('checked', false); 
            }
            $(".selectRow").attr('disabled', false);
            $(`#check_${clenaCom}`).removeClass('d-none');
            $(`#loading_${clenaCom}`).addClass('d-none');
        
        },
        error: function (e) {
            $(".selectRow").attr('checked', false);
            $(".selectRow").attr('disabled', false);
            $(`#check_${clenaCom}`).removeClass('d-none');
            $(`#loading_${clenaCom}`).addClass('d-none');
            alertaError("Error inesperado, vuelve a interlo");
        },
    });
}