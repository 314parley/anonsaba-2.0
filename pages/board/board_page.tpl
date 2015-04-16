<form id="delform" action="{{url}}post.php" method="post">
<input type="hidden" name="board" value="{{board.name}}" />
{% for postsa in posts %}
	    {% for postkey, post in postsa %}
		{% if post.parent == 0 %}
			<div id="thread{{post.id}}{{board.name}}">
				{% for file in files %}
					{% if file.id == post.id %}
						File: <a href="{{url}}{{board.name}}/src/{{file.file}}{{file.type}}">{{file.file}}</a> - ({{ '%.0f' | format(file.size/1024) }} KB, {{file.original}}{{file.type}})<br />
						<span class="thumb">
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
					{% if post.sticky == 1 %}
						<img style="border: 0;" src="{{url}}pages/css/sticky.gif" alt="Stickied" />
					{% endif %}
					{% if post.lock == 1 %}
						<img style="border: 0;" src="{{url}}pages/css/lock.gif" alt="Locked" />
					{% endif %}

				</label>
				{% if board.showid %}
					ID: {{post.ipid|slice(0, 6)}}
				{% endif %}
				[<a href="/{{board.name}}/res/{{post.id}}.html">Reply</a>]
				<span id="dnb-{{board.name}}-{{post.id}}"></span>
				<blockquote>&nbsp;&nbsp;
					{% if post.rw == 1 %}
						{{post.message|raw}}
					{% else %}
						{{post.message|nl2br}}
					{% endif %}
					{% if post.banmessage != '' %}
						<br /><br />{{post.banmessage|raw}}
					{% endif %}
				</blockquote>
				<span class="omittedposts">
					{% if post.sticky == 1 and post.replies > 1 %}
							{{post.replies - 1}} 
						{% if post.replies ==  2 %}
							Post
						{% elseif post.replies > 2 %}
							Posts
						{% endif %}
						omitted. Click Reply to view.
					{% elseif post.replies > 3 %}
							{{post.replies - 3}}
							{% if post.replies == 4 %}
								Post
							{% else %}
								Posts
							{% endif %}
							omitted. Click Reply to view.
					{% endif %}
				</span>
				<br />
		{% else %}
			<table>
				<tbody>
					<tr>
						<td class="doubledash">
							&gt;&gt;
						</td>
						<td class="reply" id="reply{{post.id}}{{board.name}}">
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
							{% for file in files %}
								{% if file.id == post.id %}
									<br />File: <a href="{{url}}{{board.name}}/src/{{file.file}}{{file.type}}">{{file.file}}</a> - ({{ '%.0f' | format(file.size/1024) }} KB, {{file.original}}{{file.type}})<br />
										<span id="post_thumb{{post.id}}">
												<img src="{{url}}{{board.name}}/thumb/{{file.file}}s{{file.type}}">
										</span>
								{% endif %}
							{% endfor %}
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
		{% endif %}
	{% endfor %}
		</div>
		<br clear="left" />
		<hr />
{% endfor %}
