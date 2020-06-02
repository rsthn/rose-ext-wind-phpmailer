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
use Rose\Arry;
use Rose\Expr;

if (!Extensions::isInstalled('Wind'))
	return;

Expr::register('mail::send', function ($args, $parts, $data)
{
	$mail = new \PHPMailer\PHPMailer\PHPMailer (true);
	$config = Configuration::getInstance()->Mail;

	$mail->isSMTP();
	$mail->isHTML(true);

	if (!$config->port)
		$config->port = 587;

	if (!$config->secure)
		$config->secure = $config->port == 587 ? 'explicit' : 'implicit';

	$mail->Host = $config->host;
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = $config->secure == 'implicit' ? 'ssl' : 'tls';
	$mail->Username = $config->username;
	$mail->Password = $config->password;
	$mail->Port = $config->port;

	$mail->From = $config->from;
	$mail->FromName = $config->from_name;

	for ($i = 1; $i < $args->length; $i++)
	{
		switch (Text::toUpperCase($args->get($i)))
		{
			case 'RCPT':
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

			case 'FROM':
				$mail->From = $args->get(++$i);
				break;

			case 'FROM-NAME':
				$mail->FromName = $args->get(++$i);
				break;

			case 'SUBJECT':
				$mail->Subject = $args->get(++$i);
				break;

			case 'BODY':
				$mail->msgHTML($args->get(++$i));
				break;
		}
	}

	$mail->send();

	return true;
});
