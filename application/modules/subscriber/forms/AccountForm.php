<?php

/**
 * HandsOn CMS Framework
 *
 * LICENÇA
 *
 * Este arquivo-fonte é propriedade da Númera Soluções e Sistemas Ltda.,
 * empresa brasileira inscrita no CNPJ/MF sob nº 08.179.010/0001-48.
 * A reprodução parcial ou total do conteúdo é expressamente vedada, conforme
 * descrição detalhada da licença, disponível no documento "docs/license.txt".
 * Se o arquivo estiver ausente, por favor entre em contato pelo email
 * license@numera.com.br para que possamos enviar uma cópia imediatamente.
 *
 * @copyright  Copyright (c) 2008 Númera Soluções e Sistemas Ltda. (http://www.numera.com.br)
 * @license    http://www.numera.com.br/license/handson     HandsOn 1.0 License
 * @version    $Id$
 */
class AccountForm extends HandsOn_Form {

    private $_autocompleteurl = null;

    function __construct($autocompleteurl,$cell = FALSE) {
        parent::__construct();
        $this->_autocompleteurl = $autocompleteurl;
        if($cell != FALSE)
        $this->_cell_phone = $cell;
    }

    public function init() {
      
        $termo = '
        O uso dos serviços do Lembre Fácil implica no pleno conhecimento e aceitação do presente Termo e Condições Gerais dos Serviços (“TERMO”) pelo ASSINANTE. Portanto, o ASSINANTE que não esteja de acordo com o presente TERMO não deverá utilizar os serviços oferecidos pelo Lembre Fácil.

        O Lembre Fácil não se responsabiliza por qualquer evento relacionado ao medicamento ou tratamento realizado, bem como interrupções ou efeitos adversos/colaterais dos medicamentos. A responsabilidade do tratamento é única e exclusiva do médico e do paciente.

        O Lembre Fácil terá o direito de modificar o presente TERMO, incluindo a Política de Privacidade, sem prévio aviso ao ASSINANTE. Ao ASSINANTE é atribuída a responsabilidade de verificar periodicamente as condições deste TERMO.

        O Lembre Fácil oferece serviços de download e envio de mensagens de texto SMS a partir de ASSINANTES que desejam ser lembrar seus usuários de alguma informação mediante assinatura de acordo com os passos descritos na tela de cadastramento.

        As informações e a periodicidade com que elas serão recebidas são cadastradas pelo próprio ASSINANTE e seu conteúdo não é de responsabilidade do Lembre Fácil.

        Uma vez realizada a assinatura o ASSINANTE poderá enviar as descrições cadastradas aos celulares de seus usuários, de acordo com o tipo de programação e respectivas tarifas.

        ACESSO AO SERVIÇO

        O Lembre Fácil prestará seus serviços única e exclusivamente aos assinantes das operadoras de telefone celular que operam no Brasil.

        O ASSINANTE deverá dispor de todo o equipamento e software necessários para se conectar ao serviço.

        O Lembre Fácil não será responsável por eventuais interrupções dos serviços que não lhe sejam atribuíveis a título de dolo ou culpa grave e/ou escapem ao seu controle técnico, tais como disfunções da rede IP ou telefônica, não sendo igualmente responsável pelos tempos de resposta.

        O Lembre Fácil poderá interromper a prestação dos serviços com o objetivo de realizar trabalhos de reparação, correções do sistema, manutenção e/ou melhoras, quando seja oportuno, por tempo indefinido. Após o retorno dos serviços, o ASSINANTE continuará conforme a programação executada.

        PROPRIEDADE INTELECTUAL E INDUSTRIAL

        O ASSINANTE deverá respeitar os direitos sobre marcas, patentes, copyright e qualquer outro direito de propriedade industrial ou intelectual do Lembre Fácil e seus terceiros afiliados.

        É expressamente proibida qualquer modificação e/ou utilização dos conteúdos que:

            * violem direitos de terceiros, tais como direito de autor e conexos;
            * atentem contra a honra e a privacidade de terceiros;
            * incitem a prática de atos ilegais e/ou violentos.

        Em nenhuma hipótese poderá ser interpretada a recepção de um produto ou serviço do Lembre Fácil por parte do ASSINANTE, a partir de sua solicitação, como uma renúncia, transmissão, cessão total ou parcial da titularidade dos direitos de propriedade intelectual ou industrial em favor do ASSINANTE.

        Os conteúdos e direitos de propriedade intelectual, assim como a programação e os desenhos da pagina WEB e WAP do Lembre Fácil e seus terceiros afiliados, se encontram plenamente protegidos pelos direitos autorais, sendo proibida sua retransmissão, disponibilização, publicação, visualização, cópia, gravação, modificação, reprodução e/ou distribuição a não ser que previamente autorizados pelo Lembre Fácil e seus terceiros afiliados por escrito.

        RESPONSABILIDADE DO USUÁRIO

        É vedada a utilização de quaisquer serviços do Lembre Fácil para fins ilegais ou não autorizados, tais como envio, retransmissão, disponibilização, publicação, visualização, cópia, gravação, modificação, reprodução e/ou distribuição dos serviços contratados.

        O ASSINANTE reconhece e aceita expressamente que o uso dos serviços não está permitido para fins comerciais e quaisquer outros fins além dos descritos neste TERMO.

        RESPONSABILIDADE do Lembre Fácil

        O Lembre Fácil é responsável pela estrutura do website através do qual será acessado o perfil de cada ASSINANTE, não sendo ela, em qualquer hipótese, a responsável pelos dados introduzidos pelos usuários em seus perfis, nem pelo uso que é feito deles.

        O Lembre Fácil se compromete a manter a disponibilidade do website pelo qual se acessa o perfil de cada usuário tanto quanto possível. Em caso de força maior, ou de caso fortuito (catástrofe natural, ato terrorista, qualquer acontecimento de natureza imprevisível - e esta lista não tem nenhum caráter exaustivo) que venha tornar total ou parcialmente, definitiva ou temporariamente indisponíveis os perfis, o Lembre Fácil não será obrigada a fornecer nenhuma indenização e sua responsabilidade não será comprometida a este título.

        Diversas medidas se segurança (geração de falsos perfis, codificações diferentes...) foram tomadas durante a concepção do sistema Lembre Fácil fim de impedir intrusões e uso fraudulento dos dados pessoais dos usuários. Apesar de todas essas medidas preventivas, o Lembre Fácil não poderá em caso algum ser considerado responsável na eventual ocorrência de roubo de dados pessoais e/ou de uso fraudulento que poderia ser feito com os dados recolhidos.

        O Lembre Fácil empregará seus maiores esforços para manter o serviço em estado operacional. No entanto, trata-se de uma obrigação de meio, não sendo oferecido pelo Lembre Fácil qualquer garantia quanto ao funcionamento ininterrupto e/ou a continuidade do serviço.
        Parágrafo único: A este título, a responsabilidade do Lembre Fácil não poderá ser invocada no caso de defeitos que poderão existir, ou de danos (custos, perda de benefícios, perda de dados ou danos diretos ou indiretos) que poderão ser provenientes do uso do serviço pelo ASSINANTE, ou ainda da impossibilidade de poder ter acesso a ele.

        O Lembre Fácil  não será responsável por perdas ou danos sofridos pelo ASSINANTE E USUÁRIO que resultem da incompatibilidade do telefone celular e/ou outros aparelhos de acesso do USUÁRIO com os serviços oferecidos pela Lembre Fácil, de acordo com este TERMO.

        A responsabilidade do Lembre Fácil se limitará aos valores e quantidades solicitadas pelo ASSINANTE dos serviços que lhe são oferecidos de acordo com este TERMO.

        O Lembre Fácil não garante a qualidade, exatidão, correção, informações ou opiniões, qualquer que seja a origem que circule pela sua rede ou nas redes das quais o ASSINANTE poderá acessar pelo Lembre Fácil. O ASSINANTE assume sob a sua exclusiva responsabilidade às conseqüências, danos ou ações derivadas da utilização da solução, assim como de sua reprodução ou difusão.

        O Lembre Fácil não será considerado responsável por atos ou danos, diretos ou indiretos, que possam ocorrer ao ASSINANTE ou terceiros como conseqüência do uso indevido do sistema disponibilizado pelo Lembre Fácil.

        O Lembre Fácil não se responsabiliza pela disponibilidade técnica e dos conteúdos nas páginas WEB e WAP de terceiros, nas quais o USUÁRIO acesse por meio de um link incluído nas páginas WEB e WAP do Lemnre Fácil.

        O Lembre Fácil não será responsável pelas perdas ou danos sofridos pelo ASSINANTE ou USUÁRIO que resultem do não cumprimento do presente TERMO.

        CANCELAMENTO DOS SERVIÇOS

        Para cancelamento da assinatura dos serviços contratados o ASSINANTE deverá enviar um e-mail para financeiro@lembrefacil.com.br solicitando o seu descadastramento.

        Após o cancelamento, o ASSINANTE deixará de receber os avisos referentes à assinatura.

        Se o ASSINANTE cancelar o serviço, independente do motivo, o Lembre Fácil não irá reembolsá-lo por qualquer tarifa paga até data do cancelamento.

        Ao confirmar seu aceite aos termos de condições gerais, através da seleção do item “sim, eu aceito”, completando seu cadastro e ativando seu perfil, o ASSINANTE aceita integralmente as presentes condições.';

        $this->addAttribs(array(
            'autocompleteurl' => $this->_autocompleteurl
            
        ));

        $this->addElement('cellPhone', 'cell_phone', array(
            'description' => 'Celular do usuário',
            //'filters'     => array('StringTrim', 'StripTags'),
            'validators' => array(
                array('StringLength', false, array(13)),
            ),
            'label' => 'Celular',
            'required' => true,
            'attribs' => array(
                //'class'     => 'formElementAutocomplete',
                'autourl' => $this->_autocompleteurl,
            )
        ));

        $this->addElement('text', 'name', array(
            'description' => 'Nome do usuário',
            'filters' => array('StringTrim', 'StripTags'),
            'label' => 'Nome',
            'required' => true,
        ));

        $this->addElement('hidden', 'link', array(
            'description' =>
            '<a onclick=\'celllist()\'>'
            . 'Veja a lista dos celulares com suporte Java.</a>',
            'label' => '',
            'decorators' => array(
                'ViewHelper',
                array('Description', array('escape' => false, 'tag' => false)),
                array('HtmlTag', array('tag' => 'dd')),
                array('Label', array('tag' => 'dt')),
                'Errors',
            ),
        ));

        $this->addElement('radio', 'in_send_option', array(
            'description' => 'Celular do usuário',
            'multiOptions' => array(
                1 => 'Para um celular com suporte para Java.',
                2 => 'Via mensagem de texto (SMS).',
                3 => 'Laboratório (SMS).'),
            'label' => 'A programação deverá ser enviada',
            'required' => true,
        ));
        
        $this->addElement('hidden', 'extra');
        $this->addElement('textarea', 'term_of_use', array(
            'attribs' => array('readonly' => 'readonly'),
            'label' => 'Termos de Uso',
            //'description' => 'Termos de Uso',
            //'filters'     => array('StringTrim', 'StripTags'),
            'value' => $termo,
        ));

        $this->addElement('multiCheckbox', 'term_of_use_check', array(
            'multiOptions' => array(1 =>
                'Li e estou de acordo com todos os termos de uso.'),
            'filters' => array('StringTrim', 'StripTags'),
                //'required'    => true,
        ));

        $this->addDisplayGroup(
                array('link', 'in_send_option', 'term_of_use', 'term_of_use_check'), 'send_options', array('legend' => 'Opções de Envio'));

        $this->addElement('text', 'code', array(
            'description' => 'Código de ativação',
            'filters' => array('StringTrim', 'StripTags'),
            'label' => 'Código',
            'required' => false,
        ));

        $this->addElement('button', 'printContrato', array(
            'attribs' => array(
                'class' => 'printContrato',
                'id' => 'printContrato',
            ),
        ));

        $this->addSubmit();
    }

}
