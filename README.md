# camagru
https://cdn.intra.42.fr/pdf/pdf/52596/en.subject.pdf

### STACK
Server-side - PHP<br>
Client-side - HTML, CSS, Javascript<br>
structure - MVC (unclean see why in Controller/css/style.css)

### LAUNCH

#### Launch without docker

From root write following command:
<pre>
php -S localhost:8000 -t Controller
</pre>

Access the website in browser on address `http://localhost:8000`.

#### Launch with docker-compose

From root write following command:
<pre>
docker-compose up
</pre>

*Docker does not work yet, here is the problem:<br>
I can connect to website from within docker container with curl but not from browser outside the container.<br>
**Everything I tried to get the address to connect from outside the container:<br>
***Docker inspect -> docker inspect $MY_CONTAINER | grep IPAddress<br>
***Docker-machine -> docker-machine ip default<br>
***Reinstall docker through different methods and follow steps all over again<br>
**Other solutions<br>
***Try the whole process over on another computer<br>
