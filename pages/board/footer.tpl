{% if not isread %}
	<div class="userdelete">
	<tbody>
	<tr>
	<td>
	Delete post
	[<input type="checkbox" name="fileonly" id="fileonly" value="on" /><label for="fileonly">File Only</label>]
	<br />Password
	<input type="password" name="postpassword" size="8" />&nbsp;<input name="delpost" value="Delete" type="submit" />


	{% if board.report == 1 %}
		<input value="Report" onclick="var o=document.getElementsByTagName('input');for(var i=0;i<o.length;i++)if(o[i].type=='checkbox' && o[i].checked && o[i].name=='post[]') return reppop('{{url}}report.php?no='+o[i].value+'&bo={{board.name}}');" type="button">	
	{% endif %}

	</td>
	</tr>
	</tbody>
	</div>
	</form>

	<script type="text/javascript"><!--
		set_delpass("delform");
	//--></script>
{% endif %}
{% if replythread == 0 %}
	<table border="1">
	<tbody>
		<tr>
			<td>
				{% if thispage == 0 %}
					Previous
				{% else %}
					<form method="get" action="{{url}}{{board.name}}/{% if (thispage-1) != 0 %}{{thispage-1}}.html{% endif %}">
						<input value="Previous" type="submit" /></form>
				{% endif %}
			</td>
			<td>
				
				{% for numbers in 0..numpages %}
				{% spaceless %}&#91;{% if numbers != thispage %}<a href="{{url}}{{board.name}}/{{numbers}}.html">{% endif %}{{numbers}}{% if numbers != thispage %}</a>{% endif %}&#93;{% endspaceless %}
				{% endfor %}	
			</td>
			<td>
				{% if thispage == numpages %}
					Next
				{% else %}
					<form method="get" action="{{url}}{{board.name}}/{{thispage+1}}.html"><input value="Next" type="submit" /></form>
				{% endif %}
	
			</td>
		</tr>
	</tbody>
	</table>
{% endif %}
<br />
	</div>
<br />
<div class="footer" style="clear: both;">
	- {{sitename}} powered by <a href="http://www.anonsaba.org" target="_top">Anonsaba {{version}}</a>
	{% if executiontime != '' %} + Took {{executiontime}}s -{% endif %}
	{% if botads != '' and board.enableads == 1 and board.boardclass == 0 %}
		<div class="content ads">
			<center> 
				{{botads|raw}}
			</center>
		</div>
	{% endif %}
	{% if nsfwbot != '' and board.enableads == 1 and board.boardclass == 1 %}
		<div class="content ads">
			<center> 
				{{nsfwbot|raw}}
			</center>
		</div>
	{% endif %}
</div>
</body>
