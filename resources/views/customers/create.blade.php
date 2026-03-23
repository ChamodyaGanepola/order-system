@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h1><i class="fas fa-user-plus"></i> Add New Customer</h1>
</div>

<div class="content-box" style="max-width: 600px;">
    <form action="/customers" method="POST"
        onsubmit="return confirm('Are you sure you want to add this customer? ');">
        @csrf

        <div class="form-group">
            <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
            <input type="text" id="full_name" name="full_name" required placeholder="Customer full name">
        </div>

        <div class="form-group">
            <label for="phone_number"><i class="fas fa-phone"></i> Phone Number *</label>
            <input type="text" id="phone_number" name="phone_number" required placeholder="e.g. +94..." maxlength="20">
        </div>

        <div class="form-group">
            <label for="phone_number_2"><i class="fas fa-phone"></i> Phone Number 2</label>
            <input type="text" id="phone_number_2" name="phone_number_2" placeholder="Secondary phone number" maxlength="20">
        </div>

        <div class="form-group">
            <label for="street_address"><i class="fas fa-map-marker-alt"></i> Address *</label>
            <textarea id="street_address" name="street_address" required placeholder="Full street address"></textarea>
        </div>

        <!-- City input -->
        <div class="form-group">
            <label>City</label>
            <input list="cityList" id="city" name="city" class="form-control"
                placeholder="Type or select city">
            <datalist id="cityList">
                <option value="Colombo"><option value="Dehiwala"><option value="Mount Lavinia"><option value="Maharagama">
                <option value="Nugegoda"><option value="Homagama"><option value="Kottawa"><option value="Piliyandala"><option value="Kesbewa">
                <option value="Gampaha"><option value="Kadawatha"><option value="Yakkala"><option value="Ja-Ela"><option value="Wattala"><option value="Negombo"><option value="Minuwangoda"><option value="Divulapitiya">
                <option value="Kalutara"><option value="Panadura"><option value="Horana"><option value="Bandaragama"><option value="Beruwala"><option value="Aluthgama">
                <option value="Kandy"><option value="Peradeniya"><option value="Katugastota"><option value="Gampola"><option value="Nawalapitiya">
                <option value="Matale"><option value="Dambulla"><option value="Nuwara Eliya"><option value="Hatton">
                <option value="Galle"><option value="Hikkaduwa"><option value="Ambalangoda"><option value="Matara"><option value="Weligama"><option value="Akuressa">
                <option value="Hambantota"><option value="Tangalle"><option value="Beliatta">
                <option value="Kurunegala"><option value="Kuliyapitiya"><option value="Puttalam"><option value="Chilaw"><option value="Wennappuwa">
                <option value="Anuradhapura"><option value="Kekirawa"><option value="Polonnaruwa">
                <option value="Badulla"><option value="Bandarawela"><option value="Ella"><option value="Moneragala">
                <option value="Ratnapura"><option value="Balangoda"><option value="Kegalle"><option value="Mawanella">
                <option value="Trincomalee"><option value="Kinniya"><option value="Batticaloa"><option value="Ampara"><option value="Kalmunai">
                <option value="Jaffna"><option value="Nallur"><option value="Vavuniya"><option value="Kilinochchi"><option value="Mannar"><option value="Mullaitivu">
            </datalist>
        </div>

        <div class="form-group">
            <label>District</label>
            <input type="text" id="district" name="district" class="form-control">
        </div>

        <div class="form-group">
            <label>Province</label>
            <input type="text" id="province" name="province" class="form-control" readonly>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Customer</button>
            <a href="/customers" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const cityInput = document.getElementById('city');
    const districtInput = document.getElementById('district');
    const provinceInput = document.getElementById('province');

    const cityMap = {
        "colombo": ["Colombo","Western"], "dehiwala": ["Colombo","Western"], "mount lavinia": ["Colombo","Western"], "maharagama": ["Colombo","Western"],
        "nugegoda": ["Colombo","Western"], "homagama": ["Colombo","Western"], "kottawa": ["Colombo","Western"], "piliyandala": ["Colombo","Western"], "kesbewa": ["Colombo","Western"],
        "gampaha": ["Gampaha","Western"], "kadawatha": ["Gampaha","Western"], "yakkala": ["Gampaha","Western"], "ja-ela": ["Gampaha","Western"], "wattala": ["Gampaha","Western"],
        "negombo": ["Gampaha","Western"], "minuwangoda": ["Gampaha","Western"], "divulapitiya": ["Gampaha","Western"],
        "kalutara": ["Kalutara","Western"], "panadura": ["Kalutara","Western"], "horana": ["Kalutara","Western"], "bandaragama": ["Kalutara","Western"], "beruwala": ["Kalutara","Western"], "aluthgama": ["Kalutara","Western"],
        "kandy": ["Kandy","Central"], "peradeniya": ["Kandy","Central"], "katugastota": ["Kandy","Central"], "gampola": ["Kandy","Central"], "nawalapitiya": ["Kandy","Central"],
        "matale": ["Matale","Central"], "dambulla": ["Matale","Central"], "nuwara eliya": ["Nuwara Eliya","Central"], "hatton": ["Nuwara Eliya","Central"],
        "galle": ["Galle","Southern"], "hikkaduwa": ["Galle","Southern"], "ambalangoda": ["Galle","Southern"], "matara": ["Matara","Southern"], "weligama": ["Matara","Southern"], "akuressa": ["Matara","Southern"],
        "hambantota": ["Hambantota","Southern"], "tangalle": ["Hambantota","Southern"], "beliatta": ["Hambantota","Southern"],
        "kurunegala": ["Kurunegala","North Western"], "kuliyapitiya": ["Kurunegala","North Western"], "puttalam": ["Puttalam","North Western"], "chilaw": ["Puttalam","North Western"], "wennappuwa": ["Puttalam","North Western"],
        "anuradhapura": ["Anuradhapura","North Central"], "kekirawa": ["Anuradhapura","North Central"], "polonnaruwa": ["Polonnaruwa","North Central"],
        "badulla": ["Badulla","Uva"], "bandarawela": ["Badulla","Uva"], "ella": ["Badulla","Uva"], "moneragala": ["Moneragala","Uva"],
        "ratnapura": ["Ratnapura","Sabaragamuwa"], "balangoda": ["Ratnapura","Sabaragamuwa"], "kegalle": ["Kegalle","Sabaragamuwa"], "mawanella": ["Kegalle","Sabaragamuwa"],
        "trincomalee": ["Trincomalee","Eastern"], "kinniya": ["Trincomalee","Eastern"], "batticaloa": ["Batticaloa","Eastern"], "ampara": ["Ampara","Eastern"], "kalmunai": ["Ampara","Eastern"],
        "jaffna": ["Jaffna","Northern"], "nallur": ["Jaffna","Northern"], "vavuniya": ["Vavuniya","Northern"], "kilinochchi": ["Kilinochchi","Northern"], "mannar": ["Mannar","Northern"], "mullaitivu": ["Mullaitivu","Northern"]
    };

    function normalize(str){ return str.toLowerCase().trim(); }

    function updateFromCity(){
        const cityKey = normalize(cityInput.value);
        if(cityMap[cityKey]){
            districtInput.value = cityMap[cityKey][0];
            provinceInput.value = cityMap[cityKey][1];
            districtInput.readOnly = true;
        } else {
            districtInput.value = '';
            provinceInput.value = '';
            districtInput.readOnly = false;
        }
    }

    cityInput.addEventListener('input', updateFromCity);
    cityInput.addEventListener('change', updateFromCity);
});
</script>
@endsection
