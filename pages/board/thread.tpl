	<form id="delform" action="{{url}}post.php" method="post">
	<input type="hidden" name="board" value="{{board.name}}" />
	{% for post in posts %}
		{% if post.parent == 0 %}
			<div id="thread{{post.id}}{{board.name}}">
	    			{% for file in files %}
					{% if file.id == post.id %}
						File: <a href="{{url}}{{board.name}}/src/{{file.file}}{{file.type}}" target="_blank">{{file.file}}</a> - ({{ '%.0f' | format(file.size/1024) }} KB, {{file.original}}{{file.type}})<br />
 							<span id="thumb{{post.id}}">
								<img src="{{url}}{{board.name}}/thumb/{{file.file}}s{{file.type}}">
							</span>
					{% endif %}
				{% endfor %}
				<a name="{{post.id}}"></a>
				<label>
					<input type="checkbox" name="post[]" value="{{post.id}}" />
					{% if post.subject != '' %}
						<span class="filetitle">
							{{post.subject|raw}}
						</span>
					{% endif %}
					{% spaceless %}
						<span class="postername">
							{% if post.email and board.postername and board.enableemail == 1 %}
								<a href="mailto:{{post.email}}">
							{% endif %}
							{% if post.name == '' and post.tripcode == '' %}
								{{board.postername}}
							{% elseif post.name == '' and post.tripcode != '' %}
								{{board.postername}} {{post.tripcode}}
							{% else %}
								{{post.name}}
							{% endif %}
							{% if post.email != '' and board.postername != ''  %}
								</a>
							{% endif %}
						</span>
						{% if post.tripcode != '' %}
							<span class="postertrip">!{{post.tripcode}}</span>
						{% endif %}
					{% endspaceless %}
					{% if post.level == 1 %}
						<span class="admin">
							&#35;&#35;&nbsp;Admin&nbsp;&#35;&#35;
						</span>
					{% elseif post.level == 2 %}
						<span class="supermod">
							&#35;&#35;&nbsp;Super Moderator&nbsp;&#35;&#35;
						</span>
					{% elseif post.level == 3 %}
						<span class="mod">
							&#35;&#35;&nbsp;Moderator&nbsp;&#35;&#35;
						</span>
					{% elseif post.level == 4 %}
						<span class="vip">
							&#35;&#35;&nbsp;VIP&nbsp;&#35;&#35;
						</span>
					{% endif %}
					{{post.time|date('m/d/y @ h:i:s A')}}
					<span class="reflink">
						{{post.reflink|raw}}
					</span>
				</label>
				{% if board.showid %}
					ID: {{post.ipid|slice(0, 6)}}
				{% endif %}
				<span id="dnb-{{board.name}}-{{post.id}}"></span>
				<blockquote>
					{% if post.rw == 1 %}
						{{post.message|raw}}
					{% else %}
						{{post.message|nl2br}}
					{% endif %}
					{% if post.banmessage != '' %}
						<br /><br />{{post.banmessage|raw}}
					{% endif %}
				</blockquote>
				<br />
		{% else %}
				<table>
					<tbody>
						<tr>
							<td class="doubledash">
								&gt;&gt;
							</td>
							<td class="reply" id="{{post.id}}{{board.name}}">
							<a name="{{post.id}}"></a>
							<label>
								<input type="checkbox" name="post[]" value="{{post.id}}" />
								{% if post.subject != '' %}
									<span class="filetitle">
										{{post.subject|raw}}
									</span>
								{% endif %}
								{% spaceless %}
									<span class="postername">
										{% if post.email and board.postername and board.enableemail == 1 %}
											<a href="mailto:{{post.email}}">
										{% endif %}
										{% if post.name == '' and post.tripcode == '' %}
											{{board.postername}}
										{% elseif post.name == '' and post.tripcode != '' %}
											{{board.postername}} {{post.tripcode}}
										{% else %}
											{{post.name}}
										{% endif %}
										{% if post.email != '' and board.postername != ''  %}
											</a>
										{% endif %}
									</span>
									{% if post.tripcode != '' %}
										<span class="postertrip">!{{post.tripcode}}</span>
									{% endif %}
								{% endspaceless %}
								{% if post.level == 1 %}
									<span class="admin">
										&#35;&#35;&nbsp;Admin&nbsp;&#35;&#35;
									</span>
								{% elseif post.level == 2 %}
									<span class="supermod">
										&#35;&#35;&nbsp;Super Moderator&nbsp;&#35;&#35;
									</span>
								{% elseif post.level == 3 %}
									<span class="mod">
										&#35;&#35;&nbsp;Moderator&nbsp;&#35;&#35;
									</span>
								{% elseif post.level == 4 %}
									<span class="vip">
										&#35;&#35;&nbsp;VIP&nbsp;&#35;&#35;
									</span>
								{% endif %}
								{{post.time|date('m/d/y @ h:i:s A')}}
								<span class="reflink">
									{{post.reflink|raw}}
								</span>
							</label>
							{% if board.showid %}
								ID: {{post.ipid|slice(0, 6)}}
							{% endif %}
							{% for file in files %}
								{% if file.id == post.id %}
									<br />&nbsp;&nbsp;&nbsp;&nbsp;File: <a href="{{url}}{{board.name}}/src/{{file.file}}{{file.type}}">{{file.file}}</a> - ({{ '%.0f' | format(file.size/1024) }} KB, {{file.original}}{{file.type}})<br />
 									<span id="thumb{{post.id}}">
										<img src="{{url}}{{board.name}}/thumb/{{file.file}}s{{file.type}}">
									</span>
								{% endif %}
							{% endfor %}
							<span id="dnb-{{board.name}}-{{post.id}}"></span>
							<blockquote>
								{% if post.rw == 1 %}
									{{post.message|raw}}
								{% else %}
									{{post.message|nl2br}}
								{% endif %}
								{% if post.banmessage != '' %}
									<br /><br />{{post.banmessage|raw}}
								{% endif %}
							</blockquote>
					</td>
				</tr>
			</tbody>
		</table>
		<br clear="left" />
		</div>
		{% endif %}
		{% if not post.sticky and post.parent == 0 and ((board.threadhours > 0 and (post.time + (board.threadhours * 3600)) < (now + 7200 ) ) or (post.deleted_time > 0 and post.deleted_time <= (now + 7200))) %}
			<span class="oldpost">
				Marked for deletion (old)
			</span>
			<br />
		{% endif %}
{% endfor %}
		<hr />
