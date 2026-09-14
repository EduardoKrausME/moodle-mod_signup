<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * signup.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['allowchanges'] = 'Permitir que o aluno troque de turma ou cancele a inscrição';
$string['allowleader'] = 'Turma pode escolher líder';
$string['allowrename'] = 'Turma pode definir nome';
$string['alreadyregistered'] = 'Você já está inscrito nesta turma.';
$string['availability'] = 'Período de inscrição';
$string['cannotchange'] = 'Esta atividade não permite trocar de turma ou cancelar a inscrição.';
$string['capacity'] = 'Vagas';
$string['changebutton'] = 'Trocar de turma';
$string['chooseleader'] = 'Escolher líder';
$string['completionconfirmed'] = 'Exigir uma inscrição confirmada';
$string['confirmed'] = 'Confirmado';
$string['currentselection'] = 'Sua inscrição';
$string['customgroupname'] = 'Nome da turma';
$string['date'] = 'Data';
$string['defaultallowleader'] = 'Permitir que cada turma escolha um líder';
$string['defaultallowleader_help'] = 'Quando ativado, o representante da turma pode escolher um líder entre os membros confirmados. O professor pode alterar o líder depois.';
$string['defaultallowrename'] = 'Permitir que cada turma defina seu próprio nome';
$string['defaultallowrename_help'] = 'Quando ativado, o representante da turma pode substituir o nome gerado automaticamente. Depois o professor pode ativar ou desativar isso individualmente para cada turma.';
$string['defaultcapacity'] = 'Máximo de alunos por turma';
$string['defaultcapacity_help'] = 'Quantidade inicial de vagas em cada turma criada automaticamente.';
$string['eventsignupcreated'] = 'Inscrição realizada';
$string['eventsignupleft'] = 'Inscrição cancelada';
$string['eventsignupviewed'] = 'Atividade de inscrição visualizada';
$string['exportcsv'] = 'Exportar CSV';
$string['firstmembercontrols'] = 'Enquanto não houver líder, o primeiro membro confirmado pode configurar a turma.';
$string['full'] = 'Lotada';
$string['generategroups'] = 'Criar';
$string['group'] = 'Turma';
$string['groupcount'] = 'Turmas';
$string['groupcount_help'] = 'Quantidade de turmas internas que serão criadas automaticamente quando a atividade for salva pela primeira vez.';
$string['groupcreation'] = 'Criação automática das turmas';
$string['groupdefaultname'] = 'Turma ';
$string['groupfull'] = 'Esta turma está lotada e a lista de espera está desativada.';
$string['groupnameempty'] = 'O nome da turma não pode ficar vazio.';
$string['groupsalreadycreated'] = 'As turmas já foram criadas. Use Gerenciar turmas dentro da atividade para alterar nomes, vagas e líderes.';
$string['groupsgeneratedonsave'] = 'As turmas exibidas na prévia serão gravadas quando a atividade for salva.';
$string['groupssaved'] = 'Turmas atualizadas.';
$string['invalidgroup'] = 'Turma inválida.';
$string['invalidleader'] = 'O líder precisa ser um membro confirmado desta turma.';
$string['joinwaitlist'] = 'Entrar na lista de espera';
$string['leader'] = 'Líder';
$string['leavebutton'] = 'Cancelar inscrição';
$string['locktimeout'] = 'A inscrição está sendo atualizada. Tente novamente.';
$string['managegroups'] = 'Gerenciar turmas';
$string['members'] = 'Membros';
$string['modulename'] = 'Inscrição em atividade';
$string['modulenameplural'] = 'Inscrições em atividades';
$string['noleader'] = 'Nenhum líder escolhido';
$string['nomembers'] = 'Nenhum participante';
$string['pluginadministration'] = 'Administração da inscrição em atividade';
$string['pluginname'] = 'Inscrição em atividade';
$string['previewempty'] = 'Informe a quantidade de turmas e o máximo de alunos e clique em Criar.';
$string['previewtitle'] = 'Turmas que serão criadas';
$string['privacy:metadata:signup_groups'] = 'Armazena a configuração das turmas internas, que pode incluir um líder escolhido.';
$string['privacy:metadata:signup_groups:leaderid'] = 'Líder escolhido para uma turma interna da atividade.';
$string['privacy:metadata:signup_members'] = 'Armazena as escolhas de inscrição e as entradas em lista de espera.';
$string['privacy:metadata:signup_members:groupid'] = 'Turma escolhida na atividade.';
$string['privacy:metadata:signup_members:status'] = 'Indica se a inscrição está confirmada ou aguardando vaga.';
$string['privacy:metadata:signup_members:timecreated'] = 'Momento em que a inscrição foi realizada.';
$string['privacy:metadata:signup_members:userid'] = 'Usuário que fez a inscrição.';
$string['report'] = 'Relatório';
$string['savegroups'] = 'Salvar turmas';
$string['seats'] = ' vagas';
$string['seatsremaining'] = ' vagas restantes';
$string['signup:addinstance'] = 'Adicionar uma nova inscrição em atividade';
$string['signup:manage'] = 'Gerenciar turmas da inscrição';
$string['signup:signup'] = 'Inscrever-se em uma turma';
$string['signup:view'] = 'Visualizar a inscrição em atividade';
$string['signup:viewreport'] = 'Visualizar relatório de inscrições';
$string['signupbutton'] = 'Inscrever-me';
$string['signupclosed'] = 'As inscrições estão fechadas neste momento.';
$string['signupended'] = 'O período de inscrição terminou.';
$string['signupleft'] = 'Sua inscrição foi cancelada.';
$string['signupnotopen'] = 'As inscrições ainda não começaram.';
$string['signupsaved'] = 'Sua inscrição foi confirmada.';
$string['status'] = 'Situação';
$string['teamsettings'] = 'Configurações da turma';
$string['teamsettingsnotallowed'] = 'Você não pode alterar as configurações desta turma.';
$string['teamsettingssaved'] = 'Configurações da turma salvas.';
$string['timeclose'] = 'Encerrar inscrições';
$string['timeopen'] = 'Abrir inscrições';
$string['waiting'] = 'Lista de espera';
$string['waitingmembers'] = 'Lista de espera';
$string['waitlist'] = 'Ativar lista de espera';
$string['waitlist_help'] = 'Quando uma turma estiver cheia, o aluno pode entrar na lista de espera. Quando surgir uma vaga, o primeiro da fila é promovido automaticamente.';
$string['waitlistsaved'] = 'Você entrou na lista de espera.';
$string['waitposition'] = 'Posição na espera: ';
