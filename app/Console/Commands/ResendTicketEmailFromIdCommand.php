<?php

namespace App\Console\Commands;

use App\Events\TicketPurchaseCompleted;
use App\Listeners\SendPurchasedTickets;
use App\Models\Invoice;
use Exception;
use Illuminate\Console\Command;

class ResendTicketEmailFromIdCommand extends Command
{
    protected $signature = 'resend:ticket-email-from-id';

    protected $description = 'Resend ticket emails to customers';

    public function handle(): void
    {
        /*
            PLAN:
            Get Invoice from id
            create invoice generated object
            create sendpurchase ticket and call handle() passing the previous created obj
        */
        $invoices = ['504f9fe1-00f0-482b-a964-431a248beaf2',
            '597669c4-79f6-48d9-b6f0-683f2dd22637',
            '27f4731b-c436-40b9-9600-186a0cdec6de',
            '32de0b07-9598-43bd-b1e7-d29ab7efb7b7',
            '2d95a37b-a2d4-41e4-822a-f6d8ce45633a',
            'e7f1f64e-542d-4c16-94b0-15ab8ce88ab7',
            '33fa5d46-b2e9-4ee7-89c7-e9aef142184f',
            '43bf4e76-0ece-45cc-a3c4-6c15ec269877',
            '694ffa70-7f85-4658-8103-d3edeed92039',
            '4637a2ad-90e1-404d-96d5-d2f525edc49a',
            '5bbf3d27-f7b8-4d0a-af28-239a161b9d08',
            'aaa93d47-511f-4eec-aabb-45f219c68aa8',
            '4eaba26a-be56-4921-b693-b6285fc69087',
            'fdbec612-14e7-4013-9c0e-4757aa555c0c',
            '8aea831e-9422-4747-8d4f-9ae3af42a151',
            '74bbed26-c1f6-493f-9b5c-6eb56d2ac6b3',
            'af16e02e-ae61-483d-9b25-54c4c53b3c7d',
            '1e27117f-9f9a-409f-aeae-a02331df6c66',
            'b9b0aaca-58ac-4745-b886-a71f4ef2c3b0',
            '83588775-558f-4571-8a2d-206c2f725edf',
            '0fb2e03f-4592-4c30-abe6-38075a28f5d9',
            'a7ea4691-f989-44b0-9aae-43ab92e0ee7a',
            '13cf3e97-da86-4930-95cb-e0b832f3fc0c',
            '301779f4-86aa-4ae2-990d-b544f419aa77',
            '150004b6-f2d0-4840-89c1-703bdf0a3058',
            '84da7507-79c0-4b90-bcb4-59a9a88c3ab5',
            '83470ee9-1046-41e9-b925-4fb0cd5bcc2f',
            'e57c4848-dc36-4cd3-a4f3-247c531f0ec7',
            'd9a7372f-63a6-4ffc-a7bd-bb4192d599ec',
            '0b21bb47-c4d2-4267-84a2-322de644aed8',
            '8785fb1a-aba1-491f-9be1-0a60b0ffb4b3',
            'c1a081cc-4fb1-4c90-add7-23e6b0a69e57',
            '6c128dc6-e591-41c9-9116-2eec239ba392',
            '9086da1d-1698-4dc9-b794-87b2b131b149',
            '351dbd77-2bbf-477a-9d5d-6c9107ea3375',
            '4f308c78-099f-4cfe-aa10-4c791f5d5ce3',
            '68cdff41-23c7-4817-8cb7-ab9bad3b03eb',
            '2f966717-8461-4a6b-a5ae-1adda649e3f6',
            '6f858a8b-739f-47f5-938c-a9c077eff41a',
            'e43eb3db-16ec-4109-bdb5-6a558022c4fd',
            'df3719ed-0505-4f4b-8846-f4081809287c',
            '0475255e-fdd1-418f-8def-9e1efdb7e163',
            '7c8a07ee-a657-4b08-91f6-abc44c0d68cf',
            '37c00447-9bb0-438c-b082-9bf22096eb80',
            '76736681-bf7a-4e5b-aff7-90cd7066196b',
            '6dc40748-4f15-4eff-8d25-28b3a468dea1',
            '1b4262d6-c98e-4ba0-b584-373b6c378804',
            '95a13dbe-acbe-44f4-898e-e22c2ab30d35',
            '9509baac-949c-4c74-8048-dcda6de7d96d',
            '47f8582c-a581-451c-a70a-3570b53a65bd',
            '2271a09d-4fb7-495c-8adb-c18fb4ef825e',
            'aff0f48d-2b80-49d1-993c-e8eaabfcb87e',
            'e4cfcf8d-b4ea-49ea-8cb8-7401043d89e4',
            '307caecd-9d53-497e-b812-024fffcc5276',
            'b84b0d95-1850-40bb-8e60-3be61d184733',
            '4860dbd9-1d2b-4d42-979c-8d3ab99725fc',
            '50510304-0d74-4646-a9d3-d2bf135e0553',
            'c460f388-99f8-4c82-ae8f-04212981149f',
            'e0af531d-375b-4bfb-bb53-354a054725fd',
            '826116d9-be62-47a4-9e27-2e3553b03890',
            '338133f4-b566-4f2b-aaad-eb47dd851eb4',
            '452c85d5-75e9-4bee-8237-757776ea0bba',
            'd1ee986e-1e47-4bc1-88fe-ee25aafb1ca1',
            '160fb255-937b-458e-86f4-00848ea63447',
            '8ad1b092-0e98-4bd9-8566-c1d96915a90f',
            'fa3a8451-63db-424b-bd99-95c190bb282e',
            '176990dc-6457-4330-a8e8-bfd16a963599',
            'e54afb7e-90ca-46d9-a648-5b944f329a82',
            'e23e4bd4-7c39-4477-b858-5752b7f71eee',
            '3d3c26cf-da23-4824-95d3-ce55f990f76b',
            '63e1b782-6248-45a7-8a65-1385d6ed3232',
            '537f8d41-8ebd-42bc-8601-5f1a5705674d',
            '2651d82a-a134-49b4-96d2-ae85dd0556e6',
            '394ab9a1-389d-42c8-84d7-1957a54aa4f4',
            '44277f36-c6ae-465a-8bf7-460e8ddb44f3',
            '3095baa3-07e3-4da5-b6d5-b8f7c5e515e4',
            'fd7e1c82-a375-4fff-85ce-26815f4e74b9',
            'b0c787ea-048f-4c35-9184-d510872694e4',
            '3ad91b29-5429-413c-83e5-b4350a711618',
            'da018145-739d-4933-9643-48bbe2a468e7',
            '7e7020ce-241b-414c-bdc8-7786015f64af',
            'd011bc37-c4fa-4462-9500-3af6c8ffc180',
            '2b967a33-e3fc-4d59-ac3b-d085a2b9e1ad',
            '21437bd2-f209-48a6-8ae8-0ef5f9e6f8f5',
            '4e2b7f4a-5510-4926-a985-09dc79558945',
            'dc73d9c6-0eea-4d5a-8578-0338fac0a557',
            '9a835871-5e50-49b3-9bd6-50368c43d24b',
            '01cb3960-915b-42af-b542-ba223588e091',
            '6ee4cbec-f951-408e-b7e7-afd89eac61df',
            '8a24195f-f750-4861-bef3-c992c96e7f04',
            'e6ee183c-6c61-4047-b0fa-eab6dc0ca33c',
            'b3026ade-7fb2-479a-ad75-b5831a14fa06',
            'c77327c6-1a4e-4931-8692-020f9bcb2520',
            '5acd9f03-928b-44db-9f4c-bcaa0ed038de',
            'b1f49fa2-f46f-42a9-8cd0-ef3d5d86efc3',
            '9f46c794-bf63-4e6d-8e11-6c134821fd23',
            '75751dd5-20e8-47f8-a1d2-05668f8dbf3d',
            'c1a170f7-38c9-4aff-afb6-04fa0957a050',
            '2fa0266d-0394-46fd-bcb0-12fd646ba2c0',
            'd6ee8a5d-fc45-4156-b24b-ad9feff6d49d',
            '13f3999e-bd88-4db1-9be3-fa8e121ed814',
            'ec25cd8d-7706-4cb5-a845-9d45ec070d64',
            '5a806a99-361e-429b-ad40-d15188dd6b59',
            'c0986b4b-b5d7-4863-ba15-1ac46f8a0bf6',
            'f9e7ecf5-fa4e-4478-90bd-b514bcb1abd5',
            '55cc91a5-2a09-4d7a-8e89-8651c8eb1550',
            '572ab8bc-ab1c-4f6f-9290-82792e61fb87',
            '0f4ff7f7-26b6-4453-94e0-70bc31ede548',
            '15267f8b-fb61-409f-a7cb-fe22e31a031f',
            '8430b730-08f0-4da3-bf05-589bfc8111aa',
            '4996d1b6-dbd8-4c4b-a648-2f5dcb680a45',
            '12d15369-3e65-422a-819c-77f193538cfa',
            'cc4e2e6f-bab7-48b2-a367-0fb5777cec23',
            'cb3cf22c-8207-42c0-8db6-eb232edb5058',
            '8817d33a-dc2b-4084-99c7-ae3d1f6cd586',
            '59bdaac4-3752-4ad0-b7cd-c079c9c6cb2a',
            '6f7bd30f-88bd-4a13-85f7-ad9b23d73264',
            'c8101d41-e831-4a50-b89a-7647ccbba989',
            '7f4f099a-d6aa-4d81-a93b-77ff5c5b0faf',
            '8f8e1fc0-aa2b-44fa-9a04-aa740360cf67',
            'fa1f5bd9-dec9-4615-90b8-51adda7b09f7',
            '5d281896-5ea6-4ba6-a292-62f75578fcfc',
            'cfc7b619-d6ac-465b-afb2-e12a25a48ffd',
            '70ac3f8d-ae47-46da-9472-d394c6b67619',
            'fc2ae45b-e777-48eb-868e-4dcd5f06855c',
            'b699c2e2-9f1f-480b-8d32-cce1270c39c9',
            '19599833-7862-45fd-913d-311ec1fbb91c',
            '50ad6e37-d982-44f5-93e5-40ddb65b23f1',
            'f88daf45-e302-42c8-bc54-15f945d270a9',
            '547933f1-2210-4ad7-8e9d-4a358838fcf1',
            '598d32ab-b6a2-44c6-864d-893c8a13482c',
            '218d483c-4902-47a1-a709-f574afd623ff',
            'f400ea67-756d-4741-8e1b-8fae986972fc',
            'acd5ecac-521d-4e04-8fd2-550ef32b18dd',
            'aeeacd17-34f6-4662-9145-3302ca54ea32',
            '3e5ebccd-2724-40ec-b4cf-75afb28a3ec4',
            'e0efc7b7-bfdc-402d-a328-249bbb451f36',
            '8493efff-c4be-4fc1-909a-326249ac6edd',
            '83918d45-b295-47bf-a53e-fe8c56bb7b2a',
            '0c315578-ab24-4bf1-ab03-97eeadb16af3',
            '4c0e546e-73a1-40f2-a02a-b94c3a74f586',
            '716f47c2-bf2c-4aa6-af1e-b69ddf870191',
            '10caace7-1d4f-4de5-a21a-54ea0f34c0d7',
            '54dcb10b-13e9-4ae4-8be3-dce48c5526e7',
            '8147f8cb-40b8-4328-b715-1053913d30dc',
            'cd689c2c-b115-482c-b548-c16b74e25d35',
            '10aeea5a-badd-42ba-86e3-4955790b9743',
            'f9bcbdf6-58aa-401a-ae38-daf191d68747',
            '7d508062-d38b-4589-a577-6d6a54370583',
            'aef0e445-4271-43c8-b5c6-5fff291567ff',
            '0b65be9d-03c2-42af-b4d7-c28f5364a56a',
            'edafaa03-4472-40f7-9264-3a81bab257c2',
            '19d8951b-a793-40d2-ae51-9bc9c2ba8beb',
            '1ff72a87-2f78-4915-80c0-3f8dd2163e96',
            '64067e18-0acc-4a3e-9bfa-fff0cba5f67d',
            'b264bf46-8b7a-434f-9b2d-211808733f8a',
            '046ee751-a501-4b1b-8306-55aa229dd84e',
            'cbae1a78-2764-40e6-ac0e-484a510ad8c2',
            '400efb7c-5480-46b0-be1e-b1c3808498c7',
            '93121b98-5e79-493a-a3e2-23b8e291eb0e',
            'f00d457b-2833-459b-8bc4-132ccdbf64a8',
            'fa43b846-55d6-45fc-9588-2c42e141a115',
            '12134a8c-5fab-4021-8f00-e96d81e88886',
            '56e45f0c-4a80-4023-b2a7-7cc57ce87034',
            '55f67689-b216-4de5-b3c3-1a4f28ce0b7b', ];

        for ($i = 0; $i < count($invoices); $i++) {
            $invoice = $invoices[$i];
            try {
                $_invoice = Invoice::where('transaction_reference', $invoice)->firstOrFail();
                $invoiceGeneratedEvent = new TicketPurchaseCompleted($_invoice, $_invoice->customer);
                $sendPurchasedTicketsListener = new SendPurchasedTickets();
                $sendPurchasedTicketsListener->handle($invoiceGeneratedEvent);
            } catch (Exception $e) {
                $this->error("Failed to resend ticket email. Reference id $invoice: " . $e->getMessage());
            }
            $this->info("Resent ticket email for Invoice Reference ID: $invoice");
        }

        $this->info('Ticket emails resent successfully.');
    }
}
