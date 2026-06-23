<?php

namespace Database\Seeders;

use App\Models\ContactModel;
use App\Models\EntityModel;
use App\Models\Settings\ArticleModel;
use App\Models\Settings\CalendarActionModel;
use App\Models\Settings\CalendarTypeModel;
use App\Models\Settings\ContactFunctionModel;
use App\Models\Settings\CountryModel;
use App\Models\Settings\VatModel;
use Illuminate\Database\Seeder;

/**
 * Dados de demonstração (países, IVA, artigos, entidades, etc.).
 * Idempotente: pode ser executado várias vezes sem duplicar registos.
 *
 * php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCountries();
        $calendarTypes = $this->seedCalendarTypes();
        $this->seedCalendarActions($calendarTypes);
        $vats = $this->seedVats();
        $this->seedArticles($vats);
        $entities = $this->seedEntities();
        $this->seedContacts($entities);
    }

    private function seedCountries(): void
    {
        $countries = [
            ['name' => 'Portugal', 'code' => 'PT'],
            ['name' => 'Brasil', 'code' => 'BR'],
            ['name' => 'Espanha', 'code' => 'ES'],
            ['name' => 'França', 'code' => 'FR'],
            ['name' => 'Alemanha', 'code' => 'DE'],
            ['name' => 'Itália', 'code' => 'IT'],
            ['name' => 'Reino Unido', 'code' => 'GB'],
            ['name' => 'Estados Unidos', 'code' => 'US'],
            ['name' => 'Canadá', 'code' => 'CA'],
            ['name' => 'México', 'code' => 'MX'],
            ['name' => 'Argentina', 'code' => 'AR'],
            ['name' => 'Angola', 'code' => 'AO'],
            ['name' => 'Moçambique', 'code' => 'MZ'],
            ['name' => 'Cabo Verde', 'code' => 'CV'],
            ['name' => 'Suíça', 'code' => 'CH'],
            ['name' => 'Bélgica', 'code' => 'BE'],
            ['name' => 'Países Baixos', 'code' => 'NL'],
            ['name' => 'Luxemburgo', 'code' => 'LU'],
            ['name' => 'Austrália', 'code' => 'AU'],
            ['name' => 'Japão', 'code' => 'JP'],
        ];

        foreach ($countries as $country) {
            CountryModel::query()->firstOrCreate(
                ['code' => $country['code']],
                ['name' => $country['name'], 'is_active' => true],
            );
        }
    }

    /**
     * @return array<string, CalendarTypeModel>
     */
    private function seedCalendarTypes(): array
    {
        $types = [
            ['name' => 'Reuniões', 'color' => '#FF5733'],
            ['name' => 'Feriados', 'color' => '#33FF57'],
            ['name' => 'Eventos Corporativos', 'color' => '#3357FF'],
            ['name' => 'Prazos', 'color' => '#F033FF'],
            ['name' => 'Tarefas Pessoais', 'color' => '#FF33F0'],
            ['name' => 'Manutenção', 'color' => '#33FFF0'],
        ];

        $result = [];
        foreach ($types as $type) {
            $result[$type['name']] = CalendarTypeModel::query()->firstOrCreate(
                ['name' => $type['name']],
                ['color' => $type['color'], 'is_active' => true],
            );
        }

        return $result;
    }

    /**
     * @param  array<string, CalendarTypeModel>  $calendarTypes
     */
    private function seedCalendarActions(array $calendarTypes): void
    {
        $actions = [
            ['name' => 'Reunião de Equipe', 'type' => 'Reuniões'],
            ['name' => 'Feriado Nacional', 'type' => 'Feriados'],
            ['name' => 'Evento de Lançamento', 'type' => 'Eventos Corporativos'],
            ['name' => 'Prazo de Entrega', 'type' => 'Prazos'],
            ['name' => 'Tarefa Urgente', 'type' => 'Tarefas Pessoais'],
            ['name' => 'Manutenção do Sistema', 'type' => 'Manutenção'],
        ];

        foreach ($actions as $action) {
            CalendarActionModel::query()->firstOrCreate(
                ['name' => $action['name']],
                [
                    'calendar_type_id' => $calendarTypes[$action['type']]->id,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<string, VatModel>
     */
    private function seedVats(): array
    {
        $rates = [
            ['name' => 'IVA Normal', 'rate' => 23.00],
            ['name' => 'IVA Intermédio', 'rate' => 13.00],
            ['name' => 'IVA Reduzido', 'rate' => 6.00],
            ['name' => 'Isento de IVA', 'rate' => 0.00],
        ];

        $result = [];
        foreach ($rates as $vat) {
            $result[$vat['name']] = VatModel::query()->firstOrCreate(
                ['name' => $vat['name']],
                ['rate' => $vat['rate'], 'is_active' => true],
            );
        }

        return $result;
    }

    /**
     * @param  array<string, VatModel>  $vats
     */
    private function seedArticles(array $vats): void
    {
        $articles = [
            [
                'reference' => 'CONS-001',
                'name' => 'Consultoria em Transformação Digital',
                'description' => 'Serviço de consultoria para transformação digital de empresas, incluindo análise de processos e recomendações de ferramentas tecnológicas.',
                'price' => 2500.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/consultoria_transformacao_digital.jpg',
                'notes' => 'Inclui relatório detalhado e 10 horas de consultoria.',
            ],
            [
                'reference' => 'CONS-002',
                'name' => 'Consultoria em Segurança da Informação',
                'description' => 'Avaliação de segurança da informação e recomendações para melhorar a proteção de dados.',
                'price' => 3000.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/consultoria_seguranca.jpg',
                'notes' => 'Inclui teste de vulnerabilidades básicas.',
            ],
            [
                'reference' => 'CONS-003',
                'name' => 'Consultoria em Cloud Computing',
                'description' => 'Consultoria para migração e otimização de infraestrutura na nuvem.',
                'price' => 3500.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/consultoria_cloud.jpg',
                'notes' => 'Inclui plano de migração e suporte inicial.',
            ],
            [
                'reference' => 'SOFT-001',
                'name' => 'Desenvolvimento de Aplicação Web Personalizada',
                'description' => 'Desenvolvimento de aplicações web sob medida para necessidades específicas do cliente.',
                'price' => 5000.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/desenvolvimento_web.jpg',
                'notes' => 'Inclui design responsivo e integração com APIs.',
            ],
            [
                'reference' => 'SOFT-002',
                'name' => 'Desenvolvimento de Aplicação Móvel',
                'description' => 'Criação de aplicativos móveis para iOS e Android.',
                'price' => 6000.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/desenvolvimento_movel.jpg',
                'notes' => 'Inclui publicação nas lojas de aplicativos.',
            ],
            [
                'reference' => 'SOFT-003',
                'name' => 'Manutenção de Software',
                'description' => 'Serviço de manutenção e atualização de software existente.',
                'price' => 1500.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/manutencao_software.jpg',
                'notes' => 'Inclui correções de bugs e atualizações de segurança.',
            ],
            [
                'reference' => 'PROD-001',
                'name' => 'Licença de Software de Gestão',
                'description' => 'Licença anual para software de gestão empresarial desenvolvido pela InovCorp.',
                'price' => 1200.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/licenca_software_gestao.jpg',
                'notes' => 'Inclui suporte técnico e atualizações.',
            ],
            [
                'reference' => 'PROD-002',
                'name' => 'Pacote de Templates para Sites',
                'description' => 'Conjunto de templates prontos para criação rápida de sites profissionais.',
                'price' => 200.00,
                'vat' => 'IVA Intermédio',
                'photo_path' => 'images/articles/pacote_templates.jpg',
                'notes' => 'Inclui 10 templates diferentes.',
            ],
            [
                'reference' => 'PROD-003',
                'name' => 'E-book: Guia de Transformação Digital',
                'description' => 'E-book com dicas e estratégias para transformação digital de empresas.',
                'price' => 50.00,
                'vat' => 'IVA Reduzido',
                'photo_path' => 'images/articles/ebook_transformacao_digital.jpg',
                'notes' => 'Disponível para download imediato.',
            ],
            [
                'reference' => 'TREI-001',
                'name' => 'Treinamento em Ferramentas de Produtividade',
                'description' => 'Treinamento presencial ou online para uso de ferramentas de produtividade.',
                'price' => 800.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/treinamento_ferramentas.jpg',
                'notes' => 'Duração: 8 horas. Certificado incluído.',
            ],
            [
                'reference' => 'TREI-002',
                'name' => 'Workshop de Inovação e Tecnologia',
                'description' => 'Workshop prático sobre inovação e aplicação de novas tecnologias em negócios.',
                'price' => 1200.00,
                'vat' => 'IVA Normal',
                'photo_path' => 'images/articles/workshop_inovacao.jpg',
                'notes' => 'Duração: 1 dia. Material incluído.',
            ],
        ];

        foreach ($articles as $article) {
            ArticleModel::query()->firstOrCreate(
                ['reference' => $article['reference']],
                [
                    'name' => $article['name'],
                    'description' => $article['description'],
                    'price' => $article['price'],
                    'vat_id' => $vats[$article['vat']]->id,
                    'photo_path' => $article['photo_path'],
                    'notes' => $article['notes'],
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return array<string, EntityModel>
     */
    private function seedEntities(): array
    {
        $portugalId = CountryModel::query()->where('code', 'PT')->value('id');

        $entities = [
            [
                'key' => 'techsolutions',
                'number' => 'ENT-000001',
                'nif' => '506123456',
                'name' => 'TechSolutions, Lda.',
                'address' => 'Rua da Inovação, 123, 3º Andar',
                'postal_code' => '4100-001',
                'city' => 'Porto',
                'phone' => '+351 22 123 4567',
                'mobile' => '+351 912 345 678',
                'website' => 'https://techsolutions.pt',
                'email' => 'contacto@techsolutions.pt',
                'notes' => 'Cliente desde 2020. Projeto atual: Migração para a cloud.',
                'is_client' => true,
                'is_supplier' => false,
            ],
            [
                'key' => 'retailtech',
                'number' => 'ENT-000002',
                'nif' => '507123456',
                'name' => 'RetailTech Solutions, Lda.',
                'address' => 'Avenida da Liberdade, 456, 2º Andar',
                'postal_code' => '1250-001',
                'city' => 'Lisboa',
                'phone' => '+351 21 345 6789',
                'mobile' => '+351 934 567 890',
                'website' => 'https://retailtechsolutions.pt',
                'email' => 'geral@retailtechsolutions.pt',
                'notes' => 'Cliente desde 2022. Projeto atual: Implementação de sistema de gestão de inventário.',
                'is_client' => true,
                'is_supplier' => false,
            ],
            [
                'key' => 'healthinnovate',
                'number' => 'ENT-000003',
                'nif' => '508987654',
                'name' => 'HealthInnovate, Unipessoal Lda.',
                'address' => 'Rua da Saúde, 789, 1º Andar',
                'postal_code' => '4000-001',
                'city' => 'Porto',
                'phone' => '+351 22 123 4567',
                'mobile' => '+351 912 345 678',
                'website' => 'https://healthinnovate.pt',
                'email' => 'info@healthinnovate.pt',
                'notes' => 'Startup focada em soluções digitais para saúde. Projeto atual: Desenvolvimento de app de monitorização de pacientes.',
                'is_client' => true,
                'is_supplier' => false,
            ],
            [
                'key' => 'industria_moderna',
                'number' => 'ENT-000004',
                'nif' => '509123457',
                'name' => 'Indústria Moderna, SA',
                'address' => 'Zona Industrial de Aveiro, Lote 23',
                'postal_code' => '3800-001',
                'city' => 'Aveiro',
                'phone' => '+351 234 567 890',
                'mobile' => '+351 961 234 567',
                'website' => 'https://industriamoderna.pt',
                'email' => 'comercial@industriamoderna.pt',
                'notes' => 'Cliente desde 2019. Projeto atual: Automação de processos industriais.',
                'is_client' => true,
                'is_supplier' => false,
            ],
            [
                'key' => 'cm_braga',
                'number' => 'ENT-000005',
                'nif' => '501234567',
                'name' => 'Câmara Municipal de Braga',
                'address' => 'Praça do Município, 1',
                'postal_code' => '4700-001',
                'city' => 'Braga',
                'phone' => '+351 253 200 200',
                'mobile' => '+351 912 345 678',
                'website' => 'https://cm-braga.pt',
                'email' => 'geral@cm-braga.pt',
                'notes' => 'Contrato para desenvolvimento de plataforma de gestão de serviços municipais.',
                'is_client' => true,
                'is_supplier' => false,
            ],
            [
                'key' => 'logitrans',
                'number' => 'ENT-000006',
                'nif' => '506543219',
                'name' => 'LogiTrans, Lda.',
                'address' => 'Rua da Logística, 321',
                'postal_code' => '2600-001',
                'city' => 'Sintra',
                'phone' => '+351 21 987 6543',
                'mobile' => '+351 934 567 890',
                'website' => 'https://logitrans.pt',
                'email' => 'logistica@logitrans.pt',
                'notes' => 'Cliente desde 2021. Projeto atual: Sistema de rastreamento de frotas.',
                'is_client' => true,
                'is_supplier' => false,
            ],
            [
                'key' => 'officesupplies',
                'number' => 'ENT-000007',
                'nif' => '507654321',
                'name' => 'OfficeSupplies, SA',
                'address' => 'Avenida dos Fornecedores, 456',
                'postal_code' => '1000-001',
                'city' => 'Lisboa',
                'phone' => '+351 21 987 6543',
                'mobile' => '+351 934 567 890',
                'website' => 'https://officesupplies.pt',
                'email' => 'vendas@officesupplies.pt',
                'notes' => 'Fornecedor de material de escritório desde 2018. Desconto de 10%.',
                'is_client' => false,
                'is_supplier' => true,
            ],
            [
                'key' => 'cloudservices',
                'number' => 'ENT-000008',
                'nif' => '508765432',
                'name' => 'CloudServices, Lda.',
                'address' => 'Rua das Nuvens, 321',
                'postal_code' => '2700-001',
                'city' => 'Amadora',
                'phone' => '+351 21 123 4567',
                'mobile' => '+351 912 345 678',
                'website' => 'https://cloudservices.pt',
                'email' => 'suporte@cloudservices.pt',
                'notes' => 'Fornecedor de serviços de cloud. Contrato anual.',
                'is_client' => false,
                'is_supplier' => true,
            ],
            [
                'key' => 'startupinova',
                'number' => 'ENT-000009',
                'nif' => '509876543',
                'name' => 'StartUpInova',
                'address' => 'Rua das Startups, 789, 1º Andar',
                'postal_code' => '3000-001',
                'city' => 'Coimbra',
                'phone' => '+351 239 123 456',
                'mobile' => '+351 961 234 567',
                'website' => 'https://startupinova.pt',
                'email' => 'info@startupinova.pt',
                'notes' => 'Start-up focada em IA. Projeto atual: Desenvolvimento de chatbot.',
                'is_client' => true,
                'is_supplier' => false,
            ],
        ];

        $result = [];
        foreach ($entities as $entity) {
            $key = $entity['key'];
            unset($entity['key']);

            $result[$key] = EntityModel::query()->firstOrCreate(
                ['nif' => $entity['nif']],
                array_merge($entity, [
                    'country_id' => $portugalId,
                    'gdpr_consent' => true,
                    'is_active' => true,
                ]),
            );
        }

        return $result;
    }

    /**
     * @param  array<string, EntityModel>  $entities
     */
    private function seedContacts(array $entities): void
    {
        $functions = [
            'commercial' => ContactFunctionModel::query()->where('name', 'Commercial')->value('id'),
            'technical' => ContactFunctionModel::query()->where('name', 'Technical')->value('id'),
            'administrative' => ContactFunctionModel::query()->where('name', 'Administrative')->value('id'),
        ];

        $contacts = [
            [
                'number' => 'CONT-0001',
                'entity' => 'retailtech',
                'first_name' => 'Ana',
                'last_name' => 'Silva',
                'function' => 'technical',
                'phone' => '+351 21 345 6789',
                'mobile' => '+351 934 567 890',
                'email' => 'ana.silva@retailtechsolutions.pt',
                'notes' => 'Diretora de TI. Responsável pelo projeto de gestão de inventário.',
            ],
            [
                'number' => 'CONT-0002',
                'entity' => 'healthinnovate',
                'first_name' => 'João',
                'last_name' => 'Pereira',
                'function' => 'commercial',
                'phone' => '+351 22 123 4567',
                'mobile' => '+351 912 345 678',
                'email' => 'joao.pereira@healthinnovate.pt',
                'notes' => 'CEO. Principal ponto de contacto para o projeto de app de monitorização.',
            ],
            [
                'number' => 'CONT-0003',
                'entity' => 'cm_braga',
                'first_name' => 'Maria',
                'last_name' => 'Fernandes',
                'function' => 'administrative',
                'phone' => '+351 253 200 200',
                'mobile' => '+351 912 345 678',
                'email' => 'maria.fernandes@cm-braga.pt',
                'notes' => 'Chefe de Divisão de Inovação. Responsável pelo projeto de plataforma de gestão de serviços municipais.',
            ],
            [
                'number' => 'CONT-0004',
                'entity' => 'officesupplies',
                'first_name' => 'Carlos',
                'last_name' => 'Oliveira',
                'function' => 'commercial',
                'phone' => '+351 21 987 6543',
                'mobile' => '+351 934 567 890',
                'email' => 'carlos.oliveira@officesupplies.pt',
                'notes' => 'Gestor de Conta. Contato principal para encomendas e suporte.',
            ],
            [
                'number' => 'CONT-0005',
                'entity' => 'cloudservices',
                'first_name' => 'Pedro',
                'last_name' => 'Gomes',
                'function' => 'technical',
                'phone' => '+351 21 123 4567',
                'mobile' => '+351 961 234 567',
                'email' => 'pedro.gomes@cloudservices.pt',
                'notes' => 'Diretor Técnico. Responsável técnico pelo suporte e manutenção.',
            ],
        ];

        foreach ($contacts as $contact) {
            ContactModel::query()->firstOrCreate(
                ['email' => $contact['email']],
                [
                    'number' => $contact['number'],
                    'entity_id' => $entities[$contact['entity']]->id,
                    'first_name' => $contact['first_name'],
                    'last_name' => $contact['last_name'],
                    'contact_function_id' => $functions[$contact['function']],
                    'phone' => $contact['phone'],
                    'mobile' => $contact['mobile'],
                    'rgpd_consent' => true,
                    'notes' => $contact['notes'],
                    'is_active' => true,
                ],
            );
        }
    }
}
