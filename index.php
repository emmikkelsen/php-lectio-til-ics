<!Doctype html>
<html>
<head>
<title>Lectio til iCalendar</title>
<script type="text/javascript">
		function lec(){
                        if (mkurl(document.getElementById('lectio').value))
                        {
                                x = mkurl(document.getElementById('lectio').value);
                                if(x.type=='laerer'){
                                        window.location='webcal://emilba.ch/projects/lectio/lectio.php?laerer='+x.laererid+'&uger=2&type=laerer&skole='+x.school;
                                }else{
                                        window.location='webcal://emilba.ch/projects/lectio/lectio.php?elev='+x.elevid+'&uger=2&skole='+x.school;
                                }
                                return true;
                        }
                        else
                        {
                                alert('Web-addressen kunne ikke forstås. Hvis fejlen forbliver, så send en mail til me@emilba.ch der er oplyst på kontakt siden.');
                        }
                        return false;
                }
		function mkurl(url){
                        var regex = /[?&]([^=#]+)=([^&#]*)/g,
                                params = {},
                                match;
                        while(match = regex.exec(url)) {
                                params[match[1]] = match[2];
                        }
                        path = url.split("/");
                        school = path[4];
                        params.school = school;
                        if((Math.floor(params.school) && Math.floor(params.elevid)) || (Math.floor(params.school) && Math.floor(params.laererid))) return params;
                        else return false;
                }
</script>
</head>
<body>
<form action="javascript:void(0)">
<input type='text' id='lectio' placeholder='URL'/>
<input type='submit' onclick="lec()" value='Lav Kalender' />
</form>
</body>
</html>
