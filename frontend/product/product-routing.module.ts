import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { ProductListComponent } from './product-list/product-list.component';
import { ProductDetailsComponent } from './../product/product-details/product-details.component';
import { CartComponent } from './cart/cart.component';
const routes: Routes = [
  {
    path:'',
    component:ProductListComponent
  },
  {
    path:'details',
    component:ProductDetailsComponent
  },
  {
    path:'cart',
    component:CartComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ProductRoutingModule { }
