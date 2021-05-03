<?php
/*
**	Rose\Ext\Wind\PHPMailer
**
**	Copyright (c) 2019-2020, RedStar Technologies, All rights reserved.
**	https://rsthn.com/
**
**	THIS LIBRARY IS PROVIDED BY REDSTAR TECHNOLOGIES "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
**	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
**	PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL REDSTAR TECHNOLOGIES BE LIABLE FOR ANY
**	DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
**	NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
**	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
**	STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
**	USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

namespace Rose\Ext\Wind;

use Rose\Errors\Error;
use Rose\Errors\ArgumentError;

use Rose\Configuration;
use Rose\Extensions;
use Rose\Text;
use Rose\Arry;
use Rose\Expr;

if (!Extensions::isInstalled('Wind'))
	return;

$phpmailer_sendmail = function ($args, $parts, $data)
{
	$mail = new \PHPMailer\PHPMailer\PHPMailer (true);
	$config = Configuration::getInstance()->Mail;

	$mail->isSMTP();
	$mail->isHTML(true);

	if (!$config->port)
		$config->port = 587;

	if (!$config->secure || $config->secure == 'true')
		$config->secure = $config->port == 587 ? 'explicit' : 'implicit';

	$mail->XMailer = null;
	$mail->CharSet = 'UTF-8';

	$mail->Host = $config->host;
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = $config->secure == 'implicit' ? 'ssl' : ($config->secure == 'explicit' ? 'tls' : '');
	$mail->Username = $config->username;
	$mail->Password = $config->password;
	$mail->Port = $config->port;

	$mail->From = $config->from;
	$mail->FromName = $config->fromName;

	if ($config->secure == 'false')
		$mail->SMTPAutoTLS = false;

	/*
	**	Do not set `unchecked` to `true` unless absolutely necessary. Because SSL security checks will be disabled.
	*/
	if ($config->unchecked == 'true')
	{
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
	}

	/*
	**	Process each argument in the input map.
	*/
	for ($i = 1; $i < $args->length; $i++)
	{
		switch (Text::toUpperCase($args->get($i)))
		{
			case 'RCPT': case 'TO':
				$value = $args->get(++$i);

				if (\Rose\typeOf($value) == 'Rose\\Arry')
				{
					$value->forEach(function($value) use(&$mail) {
						\Rose\trace('ADDING:'.$value);
						$mail->addAddress($value);
					});
				}
				else
					$mail->addAddress($value);

				break;

			case 'CC':
				$tmp = trim($args->get(++$i));
				if ($tmp) $mail->addCC($tmp);
				break;

			case 'BCC':
				$tmp = trim($args->get(++$i));
				if ($tmp) $mail->addBCC($tmp);
				break;
	
			case 'FROM':
				$mail->From = $args->get(++$i);
				break;

			case 'FROM-NAME':
				$mail->FromName = $args->get(++$i);
				break;

			case 'REPLY-TO':
				$mail->addReplyTo($args->get(++$i));
				break;
	
			case 'SUBJECT':
				$mail->Subject = $args->get(++$i);
				break;

			case 'BODY':
				$mail->msgHTML($args->get(++$i));
				break;

			case 'ATTACHMENT':
				$value = $args->get(++$i);
				if (!$value) break;

				if (\Rose\typeOf($value) == 'Rose\\Arry')
				{
					foreach ($value->__nativeArray as $value)
					{
						if (!$value) continue;

						if (\Rose\typeOf($value) == 'Rose\\Map')
						{
							if ($value->has('data'))
							{
								$mail->AddStringAttachment ($value->data, $value->name);
							}
							else if ($value->has('path'))
							{
								$mail->AddAttachment ($value->path, $value->name);
							}
						}
						else
							$mail->AddAttachment ($value);
					}
				}
				else
				{
					if (\Rose\typeOf($value) == 'Rose\\Map')
					{
						if ($value->has('data'))
						{
							$mail->AddStringAttachment ($value->data, $value->name);
						}
						else if ($value->has('path'))
						{
							$mail->AddAttachment ($value->path, $value->name);
						}
					}
					else
						$mail->AddAttachment ($value);
				}

				break;
		}
	}

	$mail->send();

	return true;
};

Expr::register('mail::send', $phpmailer_sendmail);
Expr::register('phpmailer::send', $phpmailer_sendmail);
