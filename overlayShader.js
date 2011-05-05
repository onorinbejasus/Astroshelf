uniform mat4 u_modelViewProjMatrix;
uniform mat4 u_normalMatrix;
uniform vec3 lightDir;
uniform vec4 u_color;

attribute vec3 vNormal;
attribute vec4 vTexCoord;
attribute vec4 vPosition;

varying vec2 v_texCoord;

void main()
{
	gl_Position = u_modelViewProjMatrix * vPosition;
	v_texCoord = vTexCoord.st;
	vec4 transNormal = u_normalMatrix * vec4(vNormal, 1);
}