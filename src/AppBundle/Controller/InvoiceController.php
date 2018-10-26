<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\Invoice_detail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InvoiceController extends Controller
{
    /**
     * @Route("/invoice", name="invoice_page")
     */
    public function indexAction()
    {
        return $this->render('invoice/index.html.twig');
    }
    /**
     * @Route("/invoice/form", name="form_page")
     */
    public function formAction(Request $request)
    {
        $invoice = new Invoice();
        $invoice_detail = new Invoice_detail();
        $inv[]=$invoice;
        $inv[]=$invoice_detail;
        $form = $this->createFormBuilder($inv)
        
            ->add('invoice_number', TextType::class)
            ->add('description', TextType::class)
            ->add('amount', TextType::class)
            ->add('vat_amount', TextType::class)
            ->add('total_amount', TextType::class)
            ->add('invoice_date', DateType::class, array('widget' => 'single_text'))
            ->add('save', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
           $invoice_number = $form["invoice_number"]->getData();
           $invoice_date = $form["invoice_date"]->getData();
           $invoice->setInvoiceNumber($invoice_number);
           $invoice->setInvoiceDate($invoice_date);
           $invoice->setCustomerId("5");
           $invoice_detail->setDescription($form["description"]->getData());
           $invoice_detail->setAmount($form["amount"]->getData());
           $invoice_detail->setVatAmount($form["vat_amount"]->getData());
           $invoice_detail->setTotalAmount($form["total_amount"]->getData());
           
           $em=$this->getDoctrine()->getManager();
           $em->persist($invoice);
           $em->flush();
           $invoice_detail->setInvoiceId($invoice->getID());
           $em=$this->getDoctrine()->getManager();
           $em->persist($invoice_detail);
           $em->flush();
        }

        return $this->render('invoice/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
