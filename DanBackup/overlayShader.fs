uniform sampler2D sampler2d;
uniform vec4 u_color;

varying vec2 v_texCoord;

/*void main()
{
	
	vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);
	vec4 color = texture2D(sampler2d, texCoord);
	
	if(v_texCoord.s < 0.0 || v_texCoord.t < 0.0)
	{
		color = vec4(0.0, 0.0, 0.0, 0.0);
	}
	color.rgb = u_color.rgb;
	
	color.a *= u_color.a;
	
	
	gl_FragColor = color;
}*/


float blursize = 1.0/1024.0;
void main()
{
	vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);
			
	vec4 sum = vec4(0.0);
	sum += texture2D(sampler2d, vec2(texCoord.x + 10.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 9.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 8.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 7.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 6.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 5.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 4.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 3.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + 2.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x + blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 2.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 3.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 4.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 5.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 6.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 7.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 8.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 9.0*blursize, texCoord.y));
	sum += texture2D(sampler2d, vec2(texCoord.x - 10.0*blursize, texCoord.y));
	
	//sum /= vec4(21.0);
	sum.rgb = u_color.rgb;
	sum.a = clamp(sum.a, 0.0, 1.0);
	sum.a *= u_color.a;
	gl_FragColor = sum;
	
}