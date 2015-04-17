<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<link rel="stylesheet" type="text/css" href="/pages/css/futaba.css" title="futaba" />
	<link rel="stylesheet" type="text/css" href="/pages/css/img_globals.css" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<script type="text/javascript" src="{{url}}modules/anonsaba.js"></script>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="Sat, 17 Mar 1990 00:00:01 GMT" />
	<title>{{board.desc}}</title>
</head>
<body>
<div class="navbar">
<div class="adminbar">
[<a href="{{url}}" target="_top">Home</a>]&nbsp;&nbsp;</div>
	{% for sect in boards %}
		[
	{% for brd in sect %}
		<a title="{{brd.desc}}" href="{{url}}{{brd.name}}/">{{brd.name}}</a>{% if loop.last %}{% else %} / {% endif %}
	{% endfor %}
		 ]
	{% endfor %}
</div>
<div class="logo">
	/{{board.name}}/ - {{board.desc}}
</div>
{{board.header|raw}}<br />
<center>
{% if board.class == 2 %}
	{% for nsfwad in nsfwads %}
		<img src="{{nsfwad.url}}" height="{{nsfwad.h}}" width="{{nsfwad.w}}">
	{% endfor %}
{% else %}
	{% for sfwad in sfwads %}
		<img src="{{sfwad.url}}" height="{{sfwad.h}}" width="{{sfwad.w}}">
	{% endfor %}
{% endif %}
</center>
<hr /><a name="top"></a>
